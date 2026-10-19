<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Order,OrderItem,User,Branch};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AnalyticsController extends Controller {
 private function scope(Request $r){$q=Order::query();$u=$r->user();if($u?->branch_id)$q->where('branch_id',$u->branch_id);elseif($r->filled('branch_id'))$q->where('branch_id',$r->integer('branch_id'));$from=$r->filled('from')?$r->date('from'):now()->subDays(29)->startOfDay();$to=$r->filled('to')?$r->date('to'):now()->endOfDay();return $q->whereBetween('created_at',[$from,$to]);}
 public function overview(Request $r){$q=$this->scope($r);$paid=(clone $q)->where('payment_status','paid');$customers=(clone $q)->distinct('customer_id')->count('customer_id');$cancelled=(clone $q)->where('status','cancelled')->count();$orders=$q->count();$repeat=(clone $q)->select('customer_id')->groupBy('customer_id')->havingRaw('COUNT(*) > 1')->get()->count();return ['period'=>['from'=>$q->min('created_at'),'to'=>$q->max('created_at')],'orders'=>$orders,'paid_orders'=>(clone $paid)->count(),'sales'=>(float)$paid->sum('grand_total'),'average_order_value'=>(float)($paid->count()?$paid->sum('grand_total')/$paid->count():0),'unique_customers'=>$customers,'repeat_customers'=>$repeat,'repeat_rate_percent'=>$customers?round($repeat/$customers*100,2):0,'cancellation_rate_percent'=>$orders?round($cancelled/$orders*100,2):0,'active_riders'=>User::where('role','rider')->where('is_online',true)->count()];}
 public function hourlyDemand(Request $r){return $this->scope($r)->selectRaw('HOUR(created_at) as hour, COUNT(*) as orders, SUM(CASE WHEN payment_status="paid" THEN grand_total ELSE 0 END) as sales')->groupBy('hour')->orderBy('hour')->get();}
 public function branchPerformance(Request $r){$q=$this->scope($r);return $q->select('branch_id')->selectRaw('COUNT(*) as orders')->selectRaw('SUM(CASE WHEN payment_status="paid" THEN grand_total ELSE 0 END) as sales')->selectRaw('SUM(status="cancelled") as cancelled')->with('branch:id,name,code')->groupBy('branch_id')->orderByDesc('sales')->get();}
 public function retention(Request $r){$q=$this->scope($r);$customers=(clone $q)->select('customer_id')->distinct()->pluck('customer_id');$rows=[];foreach($customers as $id){$count=(clone $q)->where('customer_id',$id)->count();$rows[]=['customer_id'=>$id,'orders'=>$count,'repeat'=>$count>1];}return ['customers'=>count($rows),'repeat_customers'=>count(array_filter($rows,fn($x)=>$x['repeat'])),'customers_detail'=>$rows];}
}
