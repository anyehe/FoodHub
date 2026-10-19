<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoyaltyAccount extends Model { protected $fillable=['user_id','points','tier']; protected $casts=['points'=>'integer']; public function user(){return $this->belongsTo(User::class);} public function transactions(){return $this->hasMany(LoyaltyTransaction::class);} }
