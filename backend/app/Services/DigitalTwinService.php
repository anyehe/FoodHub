<?php
namespace App\Services;
use App\Models\{Branch,InventoryItem,Order,Product,User};
class DigitalTwinService {
 public function snapshot(?int $branchId=null): array {
  $orders=Order::query()->when($branchId,fn($q)=>$q->where('branch_id',$branchId));
  $inventory=InventoryItem::query()->when($branchId,fn($q)=>$q->where('branch_id',$branchId));
  $riders=User::where('role','rider')->when($branchId,fn($q)=>$q->where('branch_id',$branchId));
  $active=(clone $orders)->whereNotIn('status',['completed','cancelled','delivered']);
  $status=(clone $active)->selectRaw('status,COUNT(*) total')->groupBy('status')->pluck('total','status')->all();
  $low=(clone $inventory)->whereColumn('quantity','<=','reorder_level')->count();
  $stockValue=(float)(clone $inventory)->selectRaw('COALESCE(SUM(quantity*cost),0) value')->value('value');
  $kitchenQueued=(clone $orders)->whereIn('status',['confirmed','preparing'])->count();
  $online=(clone $riders)->where('is_online',true)->count();
  return ['branches'=>Branch::when($branchId,fn($q)=>$q->whereKey($branchId))->count(),'orders_total'=>(clone $orders)->count(),'orders_active'=>$active->count(),'orders_by_status'=>$status,'sales_paid'=>(float)(clone $orders)->where('payment_status','paid')->sum('grand_total'),'riders_online'=>$online,'riders_total'=>$riders->count(),'products_active'=>Product::where('is_active',true)->count(),'inventory'=>['items'=>$inventory->count(),'low_stock'=>$low,'stock_value'=>$stockValue],'operations'=>['kitchen_queue'=>$kitchenQueued,'orders_per_online_rider'=>$online?round($active->count()/$online,2):null,'active_order_load_percent'=>$active->count()?round($active->count()/max(1,$online*12)*100,2):0]];
 }
 public function simulate(array $input,?int $branchId=null): array {
  $s=$this->snapshot($branchId);
  $ordersPerDay=max(0,(float)($input['orders_per_day'] ?? max(1,$s['orders_total'])));
  $growth=(float)($input['demand_growth_percent']??0)/100;
  $riders=max(0,(int)($input['riders']??$s['riders_online']));
  $kitchen=max(1,(int)($input['kitchen_capacity_per_hour']??30));
  $avg=max(0,(float)($input['average_order_value']??($s['orders_total']>0?$s['sales_paid']/$s['orders_total']:350)));
  $hours=min(24,max(1,(float)($input['operating_hours']??12)));
  $serviceMinutes=max(1,(float)($input['average_service_minutes']??30));
  $cancelRate=min(100,max(0,(float)($input['cancellation_rate_percent']??0)))/100;
  $projected=$ordersPerDay*(1+$growth);
  $kitchenCapacity=$kitchen*$hours*(60/$serviceMinutes);
  $deliveryCapacity=$riders*12;
  $effective=$projected*(1-$cancelRate);
  $bottleneck=$deliveryCapacity>0?min($kitchenCapacity,$deliveryCapacity):$kitchenCapacity;
  $util=$bottleneck>0?round($effective/$bottleneck*100,2):100;
  return ['baseline'=>$s,'inputs'=>['orders_per_day'=>$ordersPerDay,'demand_growth_percent'=>$growth*100,'riders'=>$riders,'kitchen_capacity_per_hour'=>$kitchen,'average_order_value'=>$avg,'operating_hours'=>$hours,'average_service_minutes'=>$serviceMinutes,'cancellation_rate_percent'=>$cancelRate*100],'projection'=>['projected_orders_per_day'=>round($projected,2),'expected_completed_orders_per_day'=>round($effective,2),'projected_sales_per_day'=>round($effective*$avg,2),'kitchen_capacity_per_day'=>round($kitchenCapacity,2),'delivery_capacity_per_day'=>$deliveryCapacity,'bottleneck_capacity_per_day'=>round($bottleneck,2),'kitchen_load_percent'=>$kitchenCapacity>0?round($effective/$kitchenCapacity*100,2):100,'delivery_load_percent'=>$deliveryCapacity>0?round($effective/$deliveryCapacity*100,2):100,'system_load_percent'=>$util,'unserved_order_estimate'=>max(0,round($effective-$bottleneck,2)),'low_stock_items'=>$s['inventory']['low_stock']]];
 }
 public function compare(array $scenarios,?int $branchId=null): array { $results=[];foreach($scenarios as $name=>$input)$results[$name]=$this->simulate($input,$branchId)['projection'];return ['scenarios'=>$results]; }
}
