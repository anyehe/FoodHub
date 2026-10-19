<?php
declare(strict_types=1);
$root=dirname(__DIR__); $checks=[]; $check=function($name,$ok)use(&$checks){$checks[]=[ $name,(bool)$ok]; echo ($ok?'PASS ':'FAIL ').$name."\n";};
$check('Production security middleware exists',is_file($root.'/backend/app/Http/Middleware/ProductionSecurityMiddleware.php'));
$check('Detailed health endpoint exists',str_contains(file_get_contents($root.'/backend/routes/api.php'),"/health/detailed"));
$check('Backup command exists',str_contains(file_get_contents($root.'/backend/routes/console.php'),"foodhub:backup"));
$check('Backup prune command exists',str_contains(file_get_contents($root.'/backend/routes/console.php'),"foodhub:backup-prune"));
$check('Load test harness exists',is_file($root.'/scripts/load-test.php'));
$check('Backup verification harness exists',is_file($root.'/scripts/backup-verify.php'));
$check('Production environment settings documented',str_contains(file_get_contents($root.'/backend/.env.example'),'BACKUP_RETENTION_DAYS'));
$check('PHP syntax production scripts',count(array_filter(glob($root.'/scripts/*.php'),fn($f)=>trim((string)shell_exec('php -l '.escapeshellarg($f).' 2>&1 | grep -c "No syntax errors detected"'))!=='1'))===0);
$failed=count(array_filter($checks,fn($x)=>!$x[1])); echo "\nFinal acceptance static gate: ".(count($checks)-$failed)."/".count($checks)." passed.\n"; exit($failed?1:0);
