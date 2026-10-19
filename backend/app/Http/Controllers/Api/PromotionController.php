<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class PromotionController extends Controller {
 public function index(Request $r){return Promotion::active()->when($r->filled('branch_id'),fn($q)=>$q->where(function($x)use($r){$x->whereNull('branch_id')->orWhere('branch_id',$r->integer('branch_id'));}))->orderByDesc('priority')->orderBy('starts_at')->paginate(20);}
 public function adminIndex(Request $r){return Promotion::with('branch')->when($r->filled('branch_id'),fn($q)=>$q->where('branch_id',$r->integer('branch_id')))->latest()->paginate(30);}
 public function store(Request $r){$d=$this->validateData($r);$d['slug']=Str::slug($d['title']).'-'.Str::lower(Str::random(5));return Promotion::create($d);}
 public function update(Request $r,Promotion $promotion){$d=$this->validateData($r,true);if(isset($d['title']))$d['slug']=Str::slug($d['title']).'-'.Str::lower(Str::random(5));$promotion->update($d);return $promotion->fresh('branch');}
 public function destroy(Promotion $promotion){$promotion->delete();return ['message'=>'Promotion deleted'];}
 private function validateData(Request $r,bool $partial=false):array { $rules=['branch_id'=>'nullable|exists:branches,id','title'=>($partial?'sometimes|':'').'required|string|max:150','description'=>'nullable|string','banner_url'=>'nullable|url|max:2048','coupon_code'=>'nullable|string|max:80','discount_type'=>'nullable|in:fixed,percent','discount_value'=>'nullable|numeric|min:0','minimum_order'=>'nullable|numeric|min:0','maximum_discount'=>'nullable|numeric|min:0','starts_at'=>'nullable|date','ends_at'=>'nullable|date|after_or_equal:starts_at','usage_limit'=>'nullable|integer|min:1','is_active'=>'sometimes|boolean','priority'=>'nullable|integer|min:0|max:10000'];return $r->validate($rules); }
}
