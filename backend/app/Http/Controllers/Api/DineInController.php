<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{RestaurantTable,Order,Product};
use App\Services\OrderService;
use Illuminate\Http\Request;
class DineInController extends Controller {
 public function table(string $token){return RestaurantTable::with('branch')->where('qr_token',$token)->firstOrFail();}
 public function order(Request $r,OrderService $orders){$d=$r->validate(['table_token'=>'required','items'=>'required|array|min:1','items.*.product_id'=>'required|exists:products,id','items.*.quantity'=>'required|integer|min:1','items.*.options'=>'nullable|array','notes'=>'nullable|string']);$table=RestaurantTable::where('qr_token',$d['table_token'])->where('status','!=','cleaning')->firstOrFail();$order=$orders->create(['branch_id'=>$table->branch_id,'fulfillment_type'=>'dine_in','payment_method'=>'cash','items'=>$d['items'],'notes'=>$d['notes']??null],$r->user()->id);$table->update(['status'=>'occupied']);return $order->load('branch');}
}
