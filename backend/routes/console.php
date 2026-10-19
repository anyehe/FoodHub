<?php
use Illuminate\Support\Facades\Artisan;

Artisan::command('foodhub:audit-prune', function () {
    $days = max(1, (int) env('AUDIT_LOG_RETENTION_DAYS', 180));
    $deleted = \App\Models\AuditLog::where('created_at', '<', now()->subDays($days))->delete();
    $this->info("Deleted {$deleted} audit log(s) older than {$days} days.");
})->purpose('Prune FoodHub audit logs according to the configured retention period.');


Artisan::command('foodhub:backup {--output=}', function () {
    $dir = base_path($this->option('output') ?: env('BACKUP_DIR', 'storage/app/backups'));
    if (!is_dir($dir)) mkdir($dir, 0750, true);
    $name = 'foodhub-'.now()->format('Ymd-His').'.sql';
    $file = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name;
    $host = env('DB_HOST', '127.0.0.1'); $port = env('DB_PORT', '3306'); $db = env('DB_DATABASE'); $user = env('DB_USERNAME'); $pass = env('DB_PASSWORD', '');
    $mysqldump = PHP_OS_FAMILY === 'Windows' ? 'mysqldump.exe' : 'mysqldump';
    $cmd = $mysqldump.' --host='.escapeshellarg($host).' --port='.escapeshellarg($port).' --user='.escapeshellarg($user);
    if ($pass !== '') $cmd .= ' --password='.escapeshellarg($pass);
    $cmd .= ' --single-transaction --routines --triggers '.escapeshellarg($db).' > '.escapeshellarg($file).' 2>&1';
    exec($cmd, $out, $code);
    if ($code !== 0 || !is_file($file) || filesize($file) < 100) { @unlink($file); $this->error('Database backup failed. Verify mysqldump and database credentials.'); return 1; }
    $this->info('Backup created: '.$file);
    return 0;
})->purpose('Create a consistent MySQL logical backup.');

Artisan::command('foodhub:backup-prune', function () {
    $days = max(1, (int) env('BACKUP_RETENTION_DAYS', 14));
    $dir = base_path(env('BACKUP_DIR', 'storage/app/backups')); $cutoff = time() - ($days * 86400); $deleted = 0;
    if (is_dir($dir)) foreach (glob($dir.'/*.sql') ?: [] as $file) if (filemtime($file) < $cutoff) { @unlink($file); $deleted++; }
    $this->info("Deleted {$deleted} backup(s) older than {$days} days.");
})->purpose('Prune old FoodHub database backups.');
