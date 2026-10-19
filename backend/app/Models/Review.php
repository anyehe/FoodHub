<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; class Review extends Model {protected $fillable=['order_id','customer_id','product_id','branch_id','rating','comment','is_public']; protected $casts=['rating'=>'integer','is_public'=>'boolean'];}
