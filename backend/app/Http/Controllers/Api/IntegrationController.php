<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Services\IntegrationStatusService;
use App\Services\Integrations\MapService;
use Illuminate\Http\Request;
class IntegrationController extends Controller
{
 public function status(IntegrationStatusService $status){ return ['integrations'=>$status->status()]; }
 public function registerDevice(Request $request){ $data=$request->validate(['token'=>'required|string|max:500','platform'=>'nullable|string|max:30','device_id'=>'nullable|string|max:150']); $token=DeviceToken::updateOrCreate(['token'=>$data['token']],['user_id'=>$request->user()->id,'platform'=>$data['platform']??null,'device_id'=>$data['device_id']??null,'last_seen_at'=>now()]); return ['message'=>'Device registered','device'=>$token]; }
 public function geocode(Request $request, MapService $maps){ $data=$request->validate(['address'=>'required|string|max:500']); return $maps->geocode($data['address']); }
 public function directions(Request $request, MapService $maps){ $data=$request->validate(['from_lat'=>'required|numeric|between:-90,90','from_lng'=>'required|numeric|between:-180,180','to_lat'=>'required|numeric|between:-90,90','to_lng'=>'required|numeric|between:-180,180']); return ['url'=>$maps->directionsUrl($data['from_lat'],$data['from_lng'],$data['to_lat'],$data['to_lng'])]; }
}
