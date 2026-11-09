<?php

return [
    //Dibaca lewat config('demo.account_password'), bukan env() langsung, agar tetap bekerja setelah `php artisan config:cache`.
    'account_password' => env('DEMO_ACCOUNT_PASSWORD'),
    ];