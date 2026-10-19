<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Promotion extends Model { protected $fillable=['branch_id','title','slug','description','banner_url','coupon_code','discount_type','discount_value','minimum_order','maximum_discount','starts_at','ends_at','usage_limit','used_count','is_active','priority']; protected $casts=['discount_value'=>'decimal:2','minimum_order'=>'decimal:2','maximum_discount'=>'decimal:2','starts_at'=>'datetime','ends_at'=>'datetime','is_active'=>'boolean']; public function branch(){return $this->belongsTo(Branch::class);} public function scopeActive($q){return $q->where('is_active',true)->where(fn($x)=>$x->whereNull('starts_at')->orWhere('starts_at','<=',now()))->where(fn($x)=>$x->whereNull('ends_at')->orWhere('ends_at','>=',now()));} }
