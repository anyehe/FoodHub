<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoyaltyTransaction extends Model { protected $fillable=['loyalty_account_id','type','points','balance_after','reference','description','order_id','created_by','metadata']; protected $casts=['points'=>'integer','balance_after'=>'integer','metadata'=>'array']; public function account(){return $this->belongsTo(LoyaltyAccount::class,'loyalty_account_id');} public function order(){return $this->belongsTo(Order::class);} public function creator(){return $this->belongsTo(User::class,'created_by');} }
