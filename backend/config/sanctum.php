<?php
return ['stateful'=>array_filter(explode(',', env('SANCTUM_STATEFUL_DOMAINS','localhost,127.0.0.1'))),'guard'=>['web'],'expiration'=>null,'token_prefix'=>env('SANCTUM_TOKEN_PREFIX','')];
