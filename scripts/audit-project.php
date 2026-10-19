<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];
$passes = 0;

function auditCheck(bool $ok, string $name): void
{
    global $failures, $passes;
    if ($ok) { $passes++; echo "PASS  {$name}\n"; }
    else { $failures[] = $name; echo "FAIL  {$name}\n"; }
}

$routes = file_get_contents($root.'/backend/routes/api.php');
preg_match_all('~\[([^]]+)::class,\s*[\"\']([^\"\']+)[\"\']\]~', $routes, $matches, PREG_SET_ORDER);
$missing = [];
foreach ($matches as $m) {
    $slash = strrchr($m[1], '\\');
    $short = $slash === false ? $m[1] : substr($slash, 1);
    $file = $root.'/backend/app/Http/Controllers/Api/'.$short.'.php';
    if (!is_file($file)) { $missing[] = $short.'::'.$m[2].' file'; continue; }
    $source = file_get_contents($file);
    if (!preg_match('/function\s+'.preg_quote($m[2], '/').'\s*\(/', $source)) $missing[] = $short.'::'.$m[2].' method';
}
auditCheck(count($missing)===0, 'All API route controller references resolve');
if ($missing) foreach ($missing as $x) echo "  - {$x}\n";

$migrations = glob($root.'/backend/database/migrations/*.php');
$names = array_map('basename', $migrations);
$uniqueNames = array_unique($names);
auditCheck(count($names)===count($uniqueNames), 'Migration filenames are unique');

$phase2 = file_get_contents($root.'/backend/tests/Feature/Phase2OperationsTest.php');
auditCheck(str_contains($phase2, "postJson('/api/v1/orders'") && str_contains($phase2, 'assertCreated()->json()'), 'Order creation feature test expects HTTP 201');

$services = file_get_contents($root.'/backend/config/services.php');
auditCheck(str_contains($services, "FOODHUB_WEBHOOK_SECRET"), 'Webhook secret comes from environment configuration');

$payment = file_get_contents($root.'/backend/app/Http/Controllers/Api/PaymentController.php');
auditCheck(str_contains($payment, 'hash_hmac') && str_contains($payment, 'getContent()'), 'Webhook verification uses HMAC over raw request content');
auditCheck(str_contains($payment, 'DB::transaction') && str_contains($payment, "'event_id'") && str_contains($payment, 'QueryException'), 'Webhook processing is transactional and duplicate-safe');

$orderService = file_get_contents($root.'/backend/app/Services/OrderService.php');
auditCheck(str_contains($orderService, "delivery' && empty(\$data['address_id'])"), 'Delivery orders require an address');
auditCheck(str_contains($orderService, 'lockForUpdate()'), 'Coupon usage is serialized during order creation');

$report = file_get_contents($root.'/backend/app/Http/Controllers/Api/ReportController.php');
$digitalTwin = file_get_contents($root.'/backend/app/Http/Controllers/Api/DigitalTwinController.php');
auditCheck(str_contains($report, '$user?->branch_id'), 'Branch manager reports are scoped to assigned branch');
auditCheck(str_contains($digitalTwin, '$r->user()->branch_id'), 'Branch manager Digital Twin is scoped to assigned branch');

$auditMiddleware = file_get_contents($root.'/backend/app/Http/Middleware/AuditRequestMiddleware.php');
$auditModel = file_get_contents($root.'/backend/app/Models/AuditLog.php');
$auditMigration = file_get_contents($root.'/backend/database/migrations/20260924000022_create_audit_logs_table.php');
$auditRoutes = file_get_contents($root.'/backend/routes/api.php');
auditCheck(str_contains($auditMiddleware, 'AuditLog::create') && str_contains($auditMiddleware, "method() !== 'GET'"), 'Mutating staff/admin API requests are audit logged');
auditCheck(str_contains($auditModel, 'payload') && str_contains($auditMigration, "json('payload')"), 'Audit log model and migration include structured payloads');
auditCheck(str_contains($auditRoutes, "throttle:10,1") && str_contains($auditRoutes, "Route::get('/audit-logs'"), 'Authentication throttling and admin audit-log access are configured');
auditCheck(str_contains($auditRoutes, "DB::select('select 1')") && str_contains($auditRoutes, "503"), 'Health endpoint verifies database availability');

$phpFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/backend'));
$lintFailures = [];
foreach ($phpFiles as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php' || str_contains((string)$file, DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR)) continue;
    $output=[]; $code=0; exec('php -l '.escapeshellarg($file->getPathname()).' 2>&1', $output, $code);
    if ($code !== 0) $lintFailures[] = str_replace($root.'/', '', $file->getPathname());
}
auditCheck(count($lintFailures)===0, 'Backend PHP syntax scan passes');
if ($lintFailures) foreach ($lintFailures as $x) echo "  - {$x}\n";

$checks = [
 ['PayPal gateway adapter exists', is_file($root.'/backend/app/Services/Integrations/PayPalGateway.php')],
 ['GCash gateway adapter exists', is_file($root.'/backend/app/Services/Integrations/GcashGateway.php')],
 ['Firebase FCM adapter exists', is_file($root.'/backend/app/Services/Integrations/FcmService.php')],
 ['Google Maps adapter exists', is_file($root.'/backend/app/Services/Integrations/MapService.php')],
 ['SMS adapter exists', is_file($root.'/backend/app/Services/Integrations/SmsService.php')],
 ['Realtime event exists', is_file($root.'/backend/app/Events/OrderRealtimeUpdated.php')],
 ['Broadcast channels exist', is_file($root.'/backend/routes/channels.php')],
 ['Broadcasting config exists', is_file($root.'/backend/config/broadcasting.php')],
 ['Reverb dependency declared', str_contains(file_get_contents($root.'/backend/composer.json'), 'laravel/reverb')],
 ['Device token migration exists', is_file($root.'/backend/database/migrations/20260924000023_create_device_tokens_table.php')],
 ['Customer tracking endpoint exists', str_contains($routes, "/orders/{order}/tracking") && str_contains(file_get_contents($root.'/backend/app/Http/Controllers/Api/OrderController.php'), 'function tracking')],
 ['Customer checkout screen exists', is_file($root.'/flutter_app/lib/screens/checkout_screen.dart')],
 ['Customer order tracking screen exists', is_file($root.'/flutter_app/lib/screens/order_tracking_screen.dart')],
 ['Customer product detail screen exists', is_file($root.'/flutter_app/lib/screens/product_detail_screen.dart')],
 ['Rider periodic location service exists', str_contains(file_get_contents($root.'/flutter_app/lib/screens/rider_screen.dart'), 'Timer.periodic')],
];
foreach($checks as [$name,$ok]) { auditCheck($ok, $name); }

$phase4 = file_get_contents($root.'/backend/app/Services/LoyaltyWalletService.php');
$digitalTwinService = file_get_contents($root.'/backend/app/Services/DigitalTwinService.php');
$phase4Routes = file_get_contents($root.'/backend/routes/api.php');
auditCheck(str_contains($phase4, 'lockForUpdate()') && str_contains($phase4, 'rewardForOrder'), 'Phase 4 wallet/loyalty service uses transactional locking and completion rewards');
auditCheck(str_contains($phase4Routes, "'/wallet'") && str_contains($phase4Routes, "'/loyalty/redeem'") && str_contains($phase4Routes, "'/promotions'"), 'Wallet, loyalty and promotion routes are registered');
auditCheck(str_contains($phase4Routes, "'/analytics/overview'") && str_contains($phase4Routes, "'/payment-mix'"), 'Advanced analytics and payment reporting routes are registered');
auditCheck(str_contains($phase4Routes, "'/compare'") && str_contains($digitalTwinService, 'unserved_order_estimate'), 'Digital Twin comparison and capacity projection are implemented');
foreach (['Wallet.php','WalletTransaction.php','LoyaltyAccount.php','LoyaltyTransaction.php','Promotion.php'] as $model) auditCheck(is_file($root.'/backend/app/Models/'.$model), 'Phase 4 model '.$model.' exists');
foreach (['wallet_screen.dart','promotions_screen.dart'] as $screen) auditCheck(is_file($root.'/flutter_app/lib/screens/'.$screen), 'Flutter Phase 4 screen '.$screen.' exists');
auditCheck(is_file($root.'/backend/tests/Feature/Phase4AdvancedOperationsTest.php'), 'Phase 4 feature tests exist');
auditCheck(str_contains(file_get_contents($root.'/flutter_app/lib/screens/checkout_screen.dart'), "value: 'wallet'"), 'Flutter checkout exposes wallet payment');

$phase5Checks = [
 ['Production security middleware exists', is_file($root.'/backend/app/Http/Middleware/ProductionSecurityMiddleware.php')],
 ['Detailed health endpoint exists', str_contains($routes, "/health/detailed")],
 ['Backup command exists', str_contains(file_get_contents($root.'/backend/routes/console.php'), 'foodhub:backup')],
 ['Backup prune command exists', str_contains(file_get_contents($root.'/backend/routes/console.php'), 'foodhub:backup-prune')],
 ['Load test harness exists', is_file($root.'/scripts/load-test.php')],
 ['Backup verification harness exists', is_file($root.'/scripts/backup-verify.php')],
 ['Phase 5 documentation exists', is_file($root.'/docs/PHASE5_PRODUCTION.md')],
 ['Production backup retention is configurable', str_contains(file_get_contents($root.'/backend/.env.example'), 'BACKUP_RETENTION_DAYS')],
 ['HTTPS enforcement setting exists', str_contains(file_get_contents($root.'/backend/.env.example'), 'APP_FORCE_HTTPS=false')],
];
foreach($phase5Checks as [$name,$ok]) auditCheck($ok, $name);

$total=$passes+count($failures);
echo "\nStatic audit result: {$passes}/{$total} checks passed.\n";
if ($failures) { echo "Failures:\n- ".implode("\n- ",$failures)."\n"; exit(1); }
echo "Static audit passed.\n";
