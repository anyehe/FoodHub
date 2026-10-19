<?php
declare(strict_types=1);
$base=rtrim(getenv('LOAD_TEST_BASE_URL') ?: 'http://127.0.0.1:8000','/'); $path=getenv('LOAD_TEST_PATH') ?: '/api/v1/health'; $n=max(1,(int)(getenv('LOAD_TEST_REQUESTS')?:100)); $con=max(1,(int)(getenv('LOAD_TEST_CONCURRENCY')?:10));
$success=0;$fail=0;$times=[];
for($i=0;$i<$n;$i++){ $ch=curl_init($base.$path); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_CONNECTTIMEOUT=>3]); $t=microtime(true); curl_exec($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $ms=(microtime(true)-$t)*1000; curl_close($ch); $times[]=$ms; if($code>=200&&$code<400)$success++;else$fail++; }
sort($times); $pct=function(float $p)use($times){$i=(int)floor(($p/100)*(count($times)-1));return $times[$i]??0;};
printf("FoodHub load smoke test: %d requests, configured concurrency=%d\n",$n,$con); printf("success=%d failed=%d p50=%.2fms p95=%.2fms max=%.2fms\n",$success,$fail,$pct(50),$pct(95),max($times)); exit($fail===0?0:1);
