<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use Installation\InstallServiceProvider;

return [
    AppServiceProvider::class,
    EventServiceProvider::class,
    InstallServiceProvider::class,
];
