<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\User;
use Illuminate\Http\Request;
class PosController extends Controller {
 public function store(Request $r,OrderService $orders){$data=$r->validate(['branch_id'=>'required|exists:branches,id','items'=>'required|array|min:1','items.*.product_id'=>'required|exists:products,id','items.*.quantity'=>'required|integer|min:1','items.*.options'=>'nullable|array','payment_method'=>'required|string','notes'=>'nullable|string','customer_id'=>'nullable|exists:users,id']);
  $branch=$r->user()->branch_id ?: (int)$data['branch_id'];
  if($r->user()->branch_id) abort_unless((int)$data['branch_id']===(int)$r->user()->branch_id,403);
  $customer=isset($data['customer_id']) ? User::whereKey($data['customer_id'])->where('role','customer')->firstOrFail() : User::firstOrCreate(['email'=>'walkin@foodhub.test'],['name'=>'Walk-in Customer','password'=>bin2hex(random_bytes(16)),'role'=>'customer','is_active'=>true]);
  $data['branch_id']=$branch;
  return $orders->create($data,$customer->id);}
}
