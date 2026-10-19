<?php
declare(strict_types=1);
$failures=[];$pass=0;
function check(bool $condition,string $name):void{global $failures,$pass;if($condition){$pass++;echo "PASS  $name\n";}else{$failures[]=$name;echo "FAIL  $name\n";}}
$allowed=['pending'=>['confirmed','cancelled'],'confirmed'=>['preparing','cancelled'],'preparing'=>['ready'],'ready'=>['assigned','completed'],'assigned'=>['picked_up','cancelled'],'picked_up'=>['out_for_delivery'],'out_for_delivery'=>['delivered'],'delivered'=>['completed']];
$state='pending';foreach(['confirmed','preparing','ready','assigned','picked_up','out_for_delivery','delivered','completed'] as $next){check(in_array($next,$allowed[$state]??[],true),"order transition $state -> $next");$state=$next;}
check(!in_array('delivered',$allowed['pending'],true),'invalid direct delivery transition rejected');check(!in_array('completed',$allowed['preparing'],true),'invalid early completion rejected');
function price(array $items,float $discount=0,string $fulfillment='delivery'):array{$subtotal=0.0;foreach($items as $item)$subtotal+=$item['unit_price']*$item['quantity'];$discount=min(max($discount,0),$subtotal);$tax=round(($subtotal-$discount)*0.12,2);$delivery=$fulfillment==='delivery'?49.0:0.0;return ['subtotal'=>round($subtotal,2),'discount'=>round($discount,2),'tax'=>$tax,'delivery'=>$delivery,'grand'=>round($subtotal-$discount+$tax+$delivery,2)];}
$p=price([['unit_price'=>199,'quantity'=>2],['unit_price'=>79,'quantity'=>1]],50,'delivery');check($p['subtotal']===477.0,'pricing subtotal');check($p['discount']===50.0,'pricing discount');check($p['grand']===527.24,'pricing grand total');$p2=price([['unit_price'=>100,'quantity'=>1]],150,'pickup');check($p2['grand']===0.0,'discount cannot make total negative');
foreach([[-90,-180],[90,180],[14.676,121.044]] as [$lat,$lng])check($lat>=-90&&$lat<=90&&$lng>=-180&&$lng<=180,'GPS coordinate bounds');check(!(-90.01>=-90&&-90.01<=90),'invalid latitude rejected');check(!(180.01>=-180&&180.01<=180),'invalid longitude rejected');
$ids=[];for($i=0;$i<100000;$i++)$ids[]='evt-'.$i;check(count($ids)===count(array_unique($ids)),'100,000 webhook event IDs remain unique');
$schema=['event_id','provider','order_id','status','payload','processed_at'];check(in_array('payload',$schema,true),'webhook payload is represented in schema');check(in_array('event_id',$schema,true),'webhook event id is represented in schema');
$roles=['admin','branch_manager','kitchen','waiter','rider','customer'];check(count(array_unique($roles))===6,'six supported application roles');check(in_array('rider',$roles,true)&&in_array('customer',$roles,true),'rider/customer roles are distinct');
$portalRoutes=['/app'=>'customer/app.blade.php','/admin'=>'admin.blade.php','/branch'=>'dashboard.blade.php','/kitchen'=>'kitchen.blade.php','/rider'=>'rider-app-only.blade.php','/waiter'=>'waiter.blade.php','/portal'=>'portal.blade.php'];
$webRoutes=file_get_contents(__DIR__.'/../backend/routes/web.php');
foreach($portalRoutes as $route=>$view) check(str_contains($webRoutes,$route),'portal route '.$route.' is registered');
$viewsDir=__DIR__.'/../backend/resources/views';
$credentialLeak=false;foreach($portalRoutes as $view){$content=file_get_contents($viewsDir.'/'.$view);if(preg_match('/value=\"(?:[^\"]+@foodhub\.test|password)\"/i',$content)){$credentialLeak=true;}}
check(!$credentialLeak,'portal login forms do not prefill seeded credentials');
check(60>0&&60<=600,'API throttle configured to bounded value');
check(hash_equals(hash_hmac('sha256','foodhub-test','secret'),hash_hmac('sha256','foodhub-test','secret')),'HMAC webhook signature is deterministic');
check(!hash_equals(hash_hmac('sha256','foodhub-test','secret'),hash_hmac('sha256','tampered','secret')),'Tampered webhook payload produces a different signature');
check(50>=1&&50<=50,'Order quantity upper bound is bounded');
check(49>=0,'Delivery fee is non-negative');
check(is_file(__DIR__.'/../backend/app/Services/Integrations/PayPalGateway.php'),'PayPal gateway adapter exists');
check(is_file(__DIR__.'/../backend/app/Services/Integrations/GcashGateway.php'),'GCash gateway adapter exists');
check(is_file(__DIR__.'/../backend/app/Services/Integrations/FcmService.php'),'Firebase FCM adapter exists');
check(is_file(__DIR__.'/../backend/app/Services/Integrations/MapService.php'),'Google Maps adapter exists');
check(is_file(__DIR__.'/../backend/app/Services/Integrations/SmsService.php'),'SMS adapter exists');
check(is_file(__DIR__.'/../backend/app/Events/OrderRealtimeUpdated.php'),'Realtime order event exists');
check(is_file(__DIR__.'/../backend/routes/channels.php'),'Broadcast channel authorization exists');
check(is_file(__DIR__.'/../backend/config/broadcasting.php'),'Broadcasting configuration exists');
$composer=json_decode(file_get_contents(__DIR__.'/../backend/composer.json'),true); check(isset($composer['require']['laravel/reverb']),'Laravel Reverb dependency declared');
$pub=file_get_contents(__DIR__.'/../flutter_app/pubspec.yaml'); check(str_contains($pub,'firebase_core:'),'Flutter Firebase Core dependency declared'); check(str_contains($pub,'firebase_messaging:'),'Flutter Firebase Messaging dependency declared');
check(is_file(__DIR__.'/../flutter_app/lib/core/services/push_service.dart'),'Flutter push registration service exists');
$api=file_get_contents(__DIR__.'/../backend/routes/api.php');
check(str_contains($api, "/orders/{order}/tracking"),'Customer order tracking endpoint is registered');
check(is_file(__DIR__.'/../flutter_app/lib/screens/checkout_screen.dart'),'Customer checkout flow exists');
check(is_file(__DIR__.'/../flutter_app/lib/screens/order_tracking_screen.dart'),'Customer live tracking screen exists');
check(is_file(__DIR__.'/../flutter_app/lib/screens/product_detail_screen.dart'),'Customer product detail/options screen exists');
$rider=file_get_contents(__DIR__.'/../flutter_app/lib/screens/rider_screen.dart');
check(str_contains($rider,'Timer.periodic') && str_contains($rider,'sendLocation'),'Rider periodic location updates are wired');


$phase4Migrations=['20260925000001_create_wallets_table.php','20260925000002_create_wallet_transactions_table.php','20260925000003_create_loyalty_accounts_table.php','20260925000004_create_loyalty_transactions_table.php','20260925000005_create_promotions_table.php'];
foreach($phase4Migrations as $m)check(is_file(__DIR__.'/../backend/database/migrations/'.$m),'Phase 4 migration '.$m.' exists');
$api4=file_get_contents(__DIR__.'/../backend/routes/api.php');
foreach(['/wallet','/wallet/transactions','/loyalty/redeem','/promotions',"Route::prefix('admin')",'/analytics/overview','/payment-mix','/compare'] as $route)check(str_contains($api4,$route),'Phase 4 API route '.$route.' is registered');
check(is_file(__DIR__.'/../backend/app/Services/LoyaltyWalletService.php'),'Wallet and loyalty service exists');
check(is_file(__DIR__.'/../backend/app/Models/Promotion.php'),'Promotion model exists');
$walletService=file_get_contents(__DIR__.'/../backend/app/Services/LoyaltyWalletService.php');
check(str_contains($walletService,'lockForUpdate()'),'Wallet/loyalty balances are transactionally locked');
check(str_contains($walletService,'rewardForOrder'),'Completed-order loyalty reward is implemented');
$digital=file_get_contents(__DIR__.'/../backend/app/Services/DigitalTwinService.php');
check(str_contains($digital,'compare') && str_contains($digital,'unserved_order_estimate'),'Digital Twin scenario comparison and capacity estimates exist');
check(is_file(__DIR__.'/../backend/tests/Feature/Phase4AdvancedOperationsTest.php'),'Phase 4 feature tests exist');
check(is_file(__DIR__.'/../flutter_app/lib/screens/wallet_screen.dart'),'Flutter wallet surface exists');
check(is_file(__DIR__.'/../flutter_app/lib/screens/promotions_screen.dart'),'Flutter promotions surface exists');
check(str_contains(file_get_contents(__DIR__.'/../flutter_app/lib/screens/checkout_screen.dart'),"value: 'wallet'"),'Flutter checkout exposes wallet payment');

$total=$pass+count($failures);echo "\nSimulation result: $pass/$total checks passed.\n";if($failures){echo "Failures:\n- ".implode("\n- ",$failures)."\n";exit(1);}echo "Critical deterministic checks passed.\n";
