<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Review,Order};
use Illuminate\Http\Request;
class ReviewController extends Controller {
 public function store(Request $r){$d=$r->validate(['order_id'=>'required|exists:orders,id','product_id'=>'nullable|exists:products,id','rating'=>'required|integer|min:1|max:5','comment'=>'nullable|string|max:1000']);$o=Order::with('items')->whereKey($d['order_id'])->where('customer_id',$r->user()->id)->whereIn('status',['delivered','completed'])->firstOrFail();$productId=$d['product_id']??null;if($productId!==null && !$o->items->contains(fn($item)=>(int)$item->product_id===(int)$productId))abort(422,'The selected product was not part of this order.');if(Review::where('order_id',$o->id)->where('customer_id',$r->user()->id)->when($productId===null,fn($q)=>$q->whereNull('product_id'),fn($q)=>$q->where('product_id',$productId))->exists())abort(422,'Review already exists.');return Review::create($d+['customer_id'=>$r->user()->id,'branch_id'=>$o->branch_id]);}
}
