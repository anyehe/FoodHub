<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Product,Address,Order};
class EndToEndOrderTest extends TestCase {
 use RefreshDatabase;
 public function test_customer_can_place_order_and_staff_can_progress_it(): void {
  $this->seed();
  $customer=User::where('role','customer')->first();$rider=User::where('role','rider')->first();$staff=User::where('role','kitchen')->first();$product=Product::first();$address=Address::where('user_id',$customer->id)->first();
  $ct=$customer->createToken('test')->plainTextToken;$st=$staff->createToken('test')->plainTextToken;$rt=$rider->createToken('test')->plainTextToken;
  $response=$this->withToken($ct)->postJson('/api/v1/orders',['branch_id'=>$product->branches()->first()->id,'address_id'=>$address->id,'fulfillment_type'=>'delivery','payment_method'=>'cash','items'=>[['product_id'=>$product->id,'quantity'=>2,'options'=>[]]]]);
  $response->assertCreated()->assertJsonPath('status','pending');$order=Order::first();
  $this->withToken($st)->postJson('/api/v1/staff/orders/'.$order->id.'/transition',['status'=>'confirmed'])->assertOk();
  $this->withToken($st)->postJson('/api/v1/staff/orders/'.$order->id.'/transition',['status'=>'preparing'])->assertOk();
  $this->withToken($st)->postJson('/api/v1/staff/orders/'.$order->id.'/transition',['status'=>'ready'])->assertOk();
  $assign=$this->withToken($st)->postJson('/api/v1/staff/orders/'.$order->id.'/assign-rider',['rider_id'=>$rider->id])->assertOk()->json('id');
  $this->withToken($rt)->postJson('/api/v1/rider/assignments/'.$assign.'/accept')->assertOk();
  $this->withToken($rt)->postJson('/api/v1/rider/assignments/'.$assign.'/pickup')->assertOk()->assertJsonPath('order.status','picked_up');
  $this->withToken($rt)->postJson('/api/v1/rider/assignments/'.$assign.'/location',['latitude'=>14.676,'longitude'=>121.044])->assertOk();
  $this->withToken($rt)->postJson('/api/v1/rider/assignments/'.$assign.'/out-for-delivery')->assertOk()->assertJsonPath('order.status','out_for_delivery');
  $this->withToken($rt)->postJson('/api/v1/rider/assignments/'.$assign.'/deliver')->assertOk();
  $this->assertDatabaseHas('orders',['id'=>$order->id,'status'=>'delivered']);
  $this->assertDatabaseHas('delivery_assignments',['id'=>$assign,'status'=>'delivered']);
 }
}
