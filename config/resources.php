<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Resource Registry
    |--------------------------------------------------------------------------
    |
    | Register all your resource classes here. The key should be the resource
    | identifier (lowercase plural) and the value should be the resource class.
    |
    */

    'users' => \App\Resources\UserResource::class,
    'roles' => \App\Resources\RoleResource::class,
    'countries' => \App\Resources\CountryResource::class,
    'timezones' => \App\Resources\TimezoneResource::class,

];
