<?php
namespace Tests\Feature;
use App\Models\{User,Product,Order};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class Phase4AdvancedOperationsTest extends TestCase {
 use RefreshDatabase;
 public function test_wallet_loyalty_promotions_and_analytics_are_available(): void {
  $this->seed();$customer=User::where('role','customer')->first();$admin=User::where('role','admin')->first();$product=Product::first();$ct=$customer->createToken('phase4-customer')->plainTextToken;$at=$admin->createToken('phase4-admin')->plainTextToken;
  $this->withToken($ct)->getJson('/api/v1/wallet')->assertOk()->assertJsonPath('wallet.currency','PHP')->assertJsonPath('loyalty.points',250);
  $this->getJson('/api/v1/promotions')->assertOk()->assertJsonStructure(['data']);
  $this->withToken($at)->postJson('/api/v1/admin/promotions',['title'=>'Weekend Deal','discount_type'=>'percent','discount_value'=>10,'priority'=>20])->assertCreated();
  $this->withToken($ct)->postJson('/api/v1/orders',['branch_id'=>$product->branches()->first()->id,'fulfillment_type'=>'pickup','payment_method'=>'wallet','items'=>[['product_id'=>$product->id,'quantity'=>1,'options'=>[]]]])->assertCreated()->assertJsonPath('payment_status','paid');
  $this->assertDatabaseHas('wallet_transactions',['type'=>'debit']);
  $this->withToken($at)->getJson('/api/v1/reports/analytics/overview')->assertOk()->assertJsonStructure(['orders','sales','unique_customers','repeat_rate_percent']);
  $this->withToken($at)->getJson('/api/v1/reports/payment-mix')->assertOk();
  $this->withToken($at)->getJson('/api/v1/reports/audit')->assertOk();
  $this->withToken($at)->getJson('/api/v1/digital-twin/snapshot')->assertOk()->assertJsonStructure(['orders_by_status','inventory','operations']);
  $this->withToken($at)->postJson('/api/v1/digital-twin/compare',['scenarios'=>['base'=>['orders_per_day'=>100,'riders'=>5],'growth'=>['orders_per_day'=>150,'riders'=>5,'demand_growth_percent'=>20]]])->assertOk()->assertJsonStructure(['scenarios.base','scenarios.growth']);
 }
 public function test_completed_paid_order_awards_loyalty_points_once(): void {
  $this->seed();$customer=User::where('role','customer')->first();$kitchen=User::where('role','kitchen')->first();$product=Product::first();$ct=$customer->createToken('loyalty-customer')->plainTextToken;$kt=$kitchen->createToken('loyalty-kitchen')->plainTextToken;
  $before=(int)$customer->loyaltyAccount->points;$order=$this->withToken($ct)->postJson('/api/v1/orders',['branch_id'=>$product->branches()->first()->id,'fulfillment_type'=>'pickup','payment_method'=>'cash','items'=>[['product_id'=>$product->id,'quantity'=>1,'options'=>[]]]])->assertCreated()->json();
  $id=$order['id'];foreach(['confirmed','preparing','ready','completed'] as $status)$this->withToken($kt)->postJson('/api/v1/staff/orders/'.$id.'/transition',['status'=>$status])->assertOk();
  $customer->refresh();$this->assertGreaterThan($before,(int)$customer->loyaltyAccount->points);$after=(int)$customer->loyaltyAccount->points;
  $this->withToken($kt)->postJson('/api/v1/staff/orders/'.$id.'/transition',['status'=>'completed'])->assertStatus(422);
  $customer->refresh();$this->assertSame($after,(int)$customer->loyaltyAccount->points);
 }
}
