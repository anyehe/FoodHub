<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\DigitalTwinService;
use Illuminate\Http\Request;
class DigitalTwinController extends Controller {
 private function branch(Request $r,?int $requested=null):?int {return $r->user()->branch_id ?: ($requested ?: null);}
 public function snapshot(Request $r,DigitalTwinService $t){return $t->snapshot($this->branch($r,$r->integer('branch_id')?:null));}
 public function simulate(Request $r,DigitalTwinService $t){$data=$r->validate(['branch_id'=>'nullable|exists:branches,id','orders_per_day'=>'nullable|numeric|min:0','demand_growth_percent'=>'nullable|numeric|min:-100','riders'=>'nullable|integer|min:0','kitchen_capacity_per_hour'=>'nullable|integer|min:1','average_order_value'=>'nullable|numeric|min:0','operating_hours'=>'nullable|numeric|min:1|max:24','average_service_minutes'=>'nullable|numeric|min:1|max:240','cancellation_rate_percent'=>'nullable|numeric|min:0|max:100']);$data['branch_id']=$this->branch($r,$data['branch_id']??null);return $t->simulate($data,$data['branch_id']);}
 public function compare(Request $r,DigitalTwinService $t){$d=$r->validate(['branch_id'=>'nullable|exists:branches,id','scenarios'=>'required|array|min:1|max:10','scenarios.*'=>'required|array']);$branch=$this->branch($r,$d['branch_id']??null);return $t->compare($d['scenarios'],$branch);}
}
