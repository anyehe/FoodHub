<?php

namespace Tests\Feature;

use App\Models\{Order, OrderStatusHistory, Product, ProductOption, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2OperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_catalog_and_coupon(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first();
        $token = $admin->createToken('test')->plainTextToken;

        $category = $this->withToken($token)->postJson('/api/v1/admin/categories', [
            'name' => 'Sides',
            'description' => 'Side dishes',
        ])->assertCreated()->json();

        $product = $this->withToken($token)->postJson('/api/v1/admin/products', [
            'category_id' => $category['id'],
            'name' => 'Garlic Fries',
            'base_price' => 99,
            'preparation_minutes' => 10,
        ])->assertOk()->json();

        $this->withToken($token)->postJson('/api/v1/admin/products/'.$product['id'].'/options', [
            'name' => 'Extra Garlic',
            'price' => 15,
        ])->assertOk();

        $this->withToken($token)->postJson('/api/v1/admin/coupons', [
            'code' => 'SAVE20',
            'type' => 'percent',
            'value' => 20,
            'minimum_order' => 100,
        ])->assertOk();

        $this->assertDatabaseHas('product_options', [
            'product_id' => $product['id'],
            'name' => 'Extra Garlic',
        ]);

        $this->assertDatabaseHas('coupons', ['code' => 'SAVE20']);
    }

    public function test_order_status_history_records_operational_changes(): void
    {
        $this->seed();
        $customer = User::where('role', 'customer')->first();
        $kitchen = User::where('role', 'kitchen')->first();
        $product = Product::first();
        $address = $customer->addresses()->first();

        $customerToken = $customer->createToken('customer-test')->plainTextToken;
        $staffToken = $kitchen->createToken('kitchen-test')->plainTextToken;

        $order = $this->withToken($customerToken)->postJson('/api/v1/orders', [
            'branch_id' => $product->branches()->first()->id,
            'address_id' => $address->id,
            'fulfillment_type' => 'delivery',
            'payment_method' => 'cash',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'options' => []]],
        ])->assertCreated()->json();

        $this->withToken($staffToken)->postJson('/api/v1/staff/orders/'.$order['id'].'/transition', [
            'status' => 'confirmed',
            'note' => 'Kitchen confirmed order',
        ])->assertOk();

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order['id'],
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order['id'],
            'status' => 'confirmed',
            'note' => 'Kitchen confirmed order',
        ]);
    }
}
