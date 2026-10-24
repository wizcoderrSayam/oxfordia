<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document Number Prefixes
    |--------------------------------------------------------------------------
    |
    | Prefixes used when generating registration, enrollment, certificate and
    | order numbers. Changing these only affects numbers generated afterwards.
    |
    */

    'prefixes' => [
        'student' => env('INSTITUTE_STUDENT_PREFIX', 'OXF'),
        'enrollment' => 'ENR',
        'certificate' => env('INSTITUTE_CERTIFICATE_PREFIX', 'OXF-CERT'),
        'order' => 'ORD',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    |
    | The payment driver used for course purchases. Only "dummy" ships with
    | this release; it simulates a gateway, enrolls without taking money and
    | is refused when APP_ENV=production.
    |
    */

    'payment_driver' => env('PAYMENT_DRIVER'),

    /*
    |--------------------------------------------------------------------------
    | Seeded Administrator
    |--------------------------------------------------------------------------
    |
    | Credentials for the development administrator created by the database
    | seeder. Set these in your local .env; nothing is seeded without them.
    |
    */

    'seed_admin' => [
        'name' => env('SEED_ADMIN_NAME', 'Administrator'),
        'email' => env('SEED_ADMIN_EMAIL'),
        'password' => env('SEED_ADMIN_PASSWORD'),
    ],

];
