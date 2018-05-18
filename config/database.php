<?php

return [

    'default' => 'pgsql',

    'connections' => [

        /**
          * POSTGRES
          */
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('PG_HOST'),
            'port' => env('PG_PORT'),
            'database' => env('PG_DATABASE'),
            'username' => env('PG_USERNAME'),
            'password' => env('PG_PASSWORD'),
            'charset' => env('PG_CHARSET'),
            'prefix' => env('PG_PREFIX'),
            'schema' => 'public'
        ]

    ]

];
