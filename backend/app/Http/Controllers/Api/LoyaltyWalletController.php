<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{LoyaltyAccount,User,Wallet,WalletTransaction};
use App\Services\LoyaltyWalletService;
use Illuminate\Http\Request;
class LoyaltyWalletController extends Controller {
 public function show(Request $r,LoyaltyWalletService $s){$u=$r->user();$w=$s->wallet($u);$l=$s->loyalty($u);return ['wallet'=>$w,'loyalty'=>$l,'redemption'=>['points'=>$s::REDEMPTION_POINTS,'php'=>$s::REDEMPTION_PHP]];}
 public function transactions(Request $r){$u=$r->user();$wallet=$u->wallet()->first();if(!$wallet)return [];return $wallet->transactions()->latest()->paginate(30);}
 public function loyaltyTransactions(Request $r){$u=$r->user();$account=$u->loyaltyAccount()->first();if(!$account)return [];return $account->transactions()->latest()->paginate(30);}
 public function redeem(Request $r,LoyaltyWalletService $s){$d=$r->validate(['points'=>'required|integer|min:100']);return $s->redeem($r->user(),$d['points']);}
 public function adminAdjust(Request $r,User $user,LoyaltyWalletService $s){$d=$r->validate(['amount'=>'required|numeric|gt:0','description'=>'nullable|string|max:255']);return $s->credit($user,(float)$d['amount'],'adjustment',$d['description']??'Admin wallet adjustment',null,$r->user());}
 public function adminLoyaltyAdjust(Request $r,User $user,LoyaltyWalletService $s){$d=$r->validate(['points'=>'required|integer|between:-100000,100000|not_in:0','description'=>'nullable|string|max:255']);return $s->adjust($user,$d['points'],$r->user(),$d['description']??'Admin loyalty adjustment');}
}
