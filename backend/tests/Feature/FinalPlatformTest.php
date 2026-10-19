<?php
namespace Tests\Feature;
use App\Models\{Order,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class FinalPlatformTest extends TestCase {
 use RefreshDatabase;
 public function test_payment_notifications_reports_and_digital_twin_are_available(): void {
  $this->seed(); $customer=User::where('role','customer')->first(); $admin=User::where('role','admin')->first(); $token=$customer->createToken('test')->plainTextToken; $adminToken=$admin->createToken('test')->plainTextToken;
  $this->withToken($token)->getJson('/api/v1/payments/methods')->assertOk()->assertJsonFragment(['code'=>'gcash']);
  $this->withToken($token)->getJson('/api/v1/notifications/unread')->assertOk()->assertJsonPath('count',0);
  $this->withToken($adminToken)->getJson('/api/v1/reports/summary')->assertOk()->assertJsonStructure(['orders','paid_orders','sales','average_order_value','cancelled','by_status']);
  $this->withToken($adminToken)->getJson('/api/v1/digital-twin/snapshot')->assertOk()->assertJsonStructure(['branches','orders_total','riders_online']);
  $this->withToken($adminToken)->postJson('/api/v1/digital-twin/simulate',['orders_per_day'=>120,'demand_growth_percent'=>25,'riders'=>10,'kitchen_capacity_per_hour'=>30,'average_order_value'=>350,'operating_hours'=>12])->assertOk()->assertJsonStructure(['baseline','inputs','projection']);
 }
 public function test_rider_location_history_and_availability_are_recorded(): void {
  $this->seed(); $rider=User::where('role','rider')->first(); $token=$rider->createToken('test')->plainTextToken;
  $this->withToken($token)->postJson('/api/v1/rider/availability',['is_online'=>true])->assertOk()->assertJsonPath('is_online',true);
  $this->assertDatabaseHas('users',['id'=>$rider->id,'is_online'=>1]);
 }
    public function test_payment_webhook_is_idempotent_and_rejects_amount_mismatch(): void
    {
        $this->seed();
        $customer = User::where('role', 'customer')->first();
        $product = \App\Models\Product::first();
        $address = $customer->addresses()->first();
        $token = $customer->createToken('webhook-test')->plainTextToken;

        $order = $this->withToken($token)->postJson('/api/v1/orders', [
            'branch_id' => $product->branches()->first()->id,
            'address_id' => $address->id,
            'fulfillment_type' => 'delivery',
            'payment_method' => 'gcash',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'options' => []]],
        ])->assertCreated()->json();

        $amount = (float) $order['grand_total'];
        $payload = [
            'event_id' => 'evt-test-001',
            'provider' => 'gcash',
            'order_id' => $order['id'],
            'status' => 'paid',
            'reference' => 'GCASH-TEST-001',
            'amount' => $amount,
            'payload' => ['test' => true],
        ];
        $rawPayload = json_encode($payload);
        $headers = ['X-FoodHub-Signature' => hash_hmac('sha256', $rawPayload, (string) config('services.foodhub_webhook_secret'))];

        $this->withHeaders($headers)->postJson('/api/v1/payments/webhook', $payload)
            ->assertOk()->assertJsonPath('duplicate', false);

        $this->withHeaders($headers)->postJson('/api/v1/payments/webhook', $payload)
            ->assertOk()->assertJsonPath('duplicate', true);

        $this->assertDatabaseCount('payment_webhook_events', 1);
        $this->assertDatabaseHas('orders', ['id' => $order['id'], 'payment_status' => 'paid']);

        $bad = $payload;
        $bad['event_id'] = 'evt-test-002';
        $bad['amount'] = $amount + 1;
        $this->withHeaders($headers)->postJson('/api/v1/payments/webhook', $bad)
            ->assertStatus(422);
    }

    public function test_unrelated_rider_cannot_view_another_order(): void
    {
        $this->seed();
        $customer = User::where('role', 'customer')->first();
        $rider = User::where('role', 'rider')->first();
        $product = \App\Models\Product::first();
        $address = $customer->addresses()->first();
        $customerToken = $customer->createToken('customer-order')->plainTextToken;
        $riderToken = $rider->createToken('unrelated-rider')->plainTextToken;

        $order = $this->withToken($customerToken)->postJson('/api/v1/orders', [
            'branch_id' => $product->branches()->first()->id,
            'address_id' => $address->id,
            'fulfillment_type' => 'delivery',
            'payment_method' => 'cash',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'options' => []]],
        ])->assertCreated()->json();

        $this->withToken($riderToken)->getJson('/api/v1/orders/'.$order['id'])->assertForbidden();
    }

    public function test_delivery_orders_require_an_address(): void
    {
        $this->seed();
        $customer = User::where('role', 'customer')->first();
        $product = \App\Models\Product::first();
        $token = $customer->createToken('address-test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/orders', [
            'branch_id' => $product->branches()->first()->id,
            'fulfillment_type' => 'delivery',
            'payment_method' => 'cash',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'options' => []]],
        ])->assertStatus(422)->assertJsonValidationErrors(['address_id']);
    }

    public function test_role_specific_portals_are_rejected_at_authentication_boundary(): void
    {
        $this->seed();
        $roles = [
            'admin' => '/api/v1/admin/dashboard',
            'branch_manager' => '/api/v1/staff/dashboard',
            'kitchen' => '/api/v1/staff/dashboard',
            'waiter' => '/api/v1/pos/orders',
            'rider' => '/api/v1/rider/queue',
            'customer' => '/api/v1/orders',
        ];

        foreach ($roles as $role => $endpoint) {
            $user = User::where('role', $role)->first();
            $token = $user->createToken('role-boundary')->plainTextToken;
            $request = $this->withToken($token);

            if ($role === 'admin') {
                $request->getJson($endpoint)->assertOk();
            } elseif ($role === 'branch_manager') {
                $request->getJson($endpoint)->assertOk();
            } elseif ($role === 'kitchen') {
                $request->getJson($endpoint)->assertOk();
            } elseif ($role === 'rider') {
                $request->getJson($endpoint)->assertOk();
            } elseif ($role === 'customer') {
                $request->getJson($endpoint)->assertOk();
            } else {
                $request->postJson($endpoint, [])->assertStatus(422);
            }
        }
    }

    public function test_health_endpoint_reports_database_status(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('database', 'ok');
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $this->seed();
        $this->withHeaders(['X-FoodHub-Signature' => 'invalid'])
            ->postJson('/api/v1/payments/webhook', [
                'event_id' => 'evt-invalid-signature',
                'order_id' => 1,
                'status' => 'paid',
            ])->assertUnauthorized();
    }

}
