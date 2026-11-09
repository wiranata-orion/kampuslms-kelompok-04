<?php

$providers = [
    App\Providers\AppServiceProvider::class,
];

// Telescope is a require-dev package; register its provider only when installed
// (it is absent in production builds using `composer install --no-dev`).
if (class_exists(\Laravel\Telescope\TelescopeApplicationServiceProvider::class)) {
    $providers[] = App\Providers\TelescopeServiceProvider::class;
}

return $providers;
