<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RiderLocationPoint extends Model {
 public $timestamps=false;
 protected $fillable=['delivery_assignment_id','rider_id','latitude','longitude','recorded_at'];
 protected $casts=['latitude'=>'float','longitude'=>'float','recorded_at'=>'datetime'];
 public function assignment(){return $this->belongsTo(DeliveryAssignment::class,'delivery_assignment_id');}
 public function rider(){return $this->belongsTo(User::class,'rider_id');}
}
