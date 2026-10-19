<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WalletTransaction extends Model { protected $fillable=['wallet_id','type','amount','balance_after','reference','description','order_id','created_by','metadata']; protected $casts=['amount'=>'decimal:2','balance_after'=>'decimal:2','metadata'=>'array']; public function wallet(){return $this->belongsTo(Wallet::class);} public function order(){return $this->belongsTo(Order::class);} public function creator(){return $this->belongsTo(User::class,'created_by');} }
