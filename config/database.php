<?php

return [

    'default' => 'pgsql',

    'connections' => [

        /**
          * POSTGRES
          */
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('POSTGRES_HOST'),
            'port' => env('POSTGRES_PORT'),
            'database' => env('POSTGRES_DB'),
            'username' => env('POSTGRES_USER'),
            'password' => env('POSTGRES_PASSWORD'),
            'charset' => env('POSTGRES_CHARSET'),
            'prefix' => env('POSTGRES_PREFIX'),
            'schema' => 'public'
        ]
    ]
];
