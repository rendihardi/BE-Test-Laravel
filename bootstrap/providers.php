<?php

use App\Providers\AppServiceProvider;
use App\Providers\RepositoryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

return [
    AppServiceProvider::class,
    RepositoryServiceProvider::class,
    PermissionServiceProvider::class,
];
