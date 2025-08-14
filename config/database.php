<?php

$DB_LOCALHOST = '127.0.0.1';

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
     */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
     */

    'connections' => [

//         'sqlite' => [
        //             'driver' => 'sqlite',
        //             'database' => env('DB_DATABASE', database_path('database.sqlite')),
        //             'prefix' => env('DB_PREFIX', ''),
        //         ],
        // read / write DB settings, @kay, projectx Feb 2020
        'mysql' => [
            'driver' => 'mysql',
            // 'url' => env('DATABASE_URL'),
            'read' => [
                'host' => env('DB_HOST_READ', $DB_LOCALHOST),
                'port' => env('DB_PORT_READ', '8889'),
                'database' => env('DB_DATABASE_READ', 'forge'),
                'username' => env('DB_USERNAME_READ', 'forge'),
                'password' => env('DB_PASSWORD_READ', ''),
            ],
            'write' => [
                'host' => env('DB_HOST', $DB_LOCALHOST),
                'port' => env('DB_PORT', '8889'),
                'database' => env('DB_DATABASE', 'forge'),
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
            ],
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', true),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
            // 'options' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
     */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
     */

    // 'redis' => [

    //     'client' => env('REDIS_CLIENT', 'phpredis'),

    //     'options' => [
    //         'cluster' => env('REDIS_CLUSTER', 'redis'),
            //'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'lumen'), '_').'_database_'),
    //     ],

    //     'default' => $redisMain = [
    //         'host' => env('REDIS_HOST', '127.0.0.1'),
    //         'user' => env('REDIS_USER', null),
    //         'pass' => env('REDIS_PASSWORD', null),
    //         'port' => env('REDIS_PORT', '6379'),
    //         'database' => env('REDIS_DB', '1'),
    //         'prefix' => env('REDIS_PREFIX', 'redis:' . env('APP_ENV', 'local')),
    //     ],

    //     'cache' => $redisCache = array_merge($redisMain, [
    //         'database' => env('REDIS_CACHE_DB', '2'),
    //     ]),

    //     'lock' => $redisLock = array_merge($redisMain, [
    //         'database' => env('REDIS_LOCK_DB', '3'),
    //     ]),

    //     'list' => $redisList = array_merge($redisMain, [
    //         'database' => env('REDIS_LIST_DB', '4'),
    //     ]),

    //     'sorted_set' => $redisSortedSet = array_merge($redisMain, [
    //         'database' => env('REDIS_SORTED_SET_DB', '5'),
    //     ]),

    //     'counter' => $redisCounter = array_merge($redisMain, [
    //         'database' => env('REDIS_COUNTER_DB', '6'),
    //     ]),
    // ],

    'datetime_format' => env('DB_DATETIME_FORMAT', 'Y-m-d\TH:i:s'),
];
