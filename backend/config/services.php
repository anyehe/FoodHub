<?php
return [
 'gcash'=>[
   'secret'=>env('GCASH_SECRET'),
   'checkout_url'=>env('GCASH_CHECKOUT_URL'),
   'return_url'=>env('GCASH_RETURN_URL'),
   'cancel_url'=>env('GCASH_CANCEL_URL'),
 ],
 'paypal'=>[
   'client_id'=>env('PAYPAL_CLIENT_ID'), 'secret'=>env('PAYPAL_SECRET'),
   'mode'=>env('PAYPAL_MODE','sandbox'), 'currency'=>env('PAYPAL_CURRENCY','PHP'),
   'return_url'=>env('PAYPAL_RETURN_URL'), 'cancel_url'=>env('PAYPAL_CANCEL_URL'),
 ],
 'google_maps'=>['key'=>env('GOOGLE_MAPS_KEY')],
 'firebase'=>[
   'project_id'=>env('FIREBASE_PROJECT_ID'),
   'client_email'=>env('FIREBASE_CLIENT_EMAIL'),
   'private_key'=>env('FIREBASE_PRIVATE_KEY'),
   'service_account_json'=>env('FIREBASE_SERVICE_ACCOUNT_JSON'),
   'credentials_path'=>env('GOOGLE_APPLICATION_CREDENTIALS'),
 ],
 'sms'=>['url'=>env('SMS_PROVIDER_URL'),'token'=>env('SMS_PROVIDER_TOKEN')],
 'foodhub_webhook_secret'=>env('FOODHUB_WEBHOOK_SECRET'),
];
