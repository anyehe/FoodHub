<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class NotificationController extends Controller {
 public function index(Request $request){return $request->user()->notifications()->latest()->paginate(30);}
 public function unread(Request $request){return ['count'=>$request->user()->unreadNotifications()->count()];}
 public function read(Request $request,string $id){$n=$request->user()->notifications()->whereKey($id)->firstOrFail();$n->markAsRead();return ['message'=>'Notification marked as read'];}
 public function readAll(Request $request){$request->user()->unreadNotifications->markAsRead();return ['message'=>'Notifications marked as read'];}
}
