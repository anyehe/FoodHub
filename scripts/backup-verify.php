<?php
declare(strict_types=1);
$dir=dirname(__DIR__).'/'.(getenv('BACKUP_DIR')?:'backend/storage/app/backups'); $files=glob($dir.'/*.sql')?:[]; if(!$files){echo "No SQL backups found in {$dir}.\n"; exit(1);} rsort($files); $file=$files[0]; $size=filesize($file); $head=file_get_contents($file,false,null,0,min(4096,$size)); $ok=$size>100 && (str_contains($head,'MySQL') || str_contains($head,'MariaDB') || str_contains($head,'CREATE')); echo "Latest backup: {$file} ({$size} bytes)\n"; echo $ok?"Backup structural check: PASS\n":"Backup structural check: FAIL\n"; exit($ok?0:1);
