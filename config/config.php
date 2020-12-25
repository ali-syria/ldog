<?php

return [
    'domain'=> env('DOMAIN','data.example'),
    'graph_stores'=> [
        'open'=> [
            'driver'=> env('GS_OPEN_DRIVER','graph_db'),
            'host'=> env('GS_OPEN_HOST','http://10.0.2.2:7200'),
            'repository'=> env('GS_OPEN_REPOSITORY','open'),
            'username'=> env('GS_OPEN_USERNAME','admin'),
            'password'=> env('GS_OPEN_PASSWORD','root'),
        ],
        'secure'=> [
            'driver'=> env('GS_SECURE_DRIVER','graph_db'),
            'host'=> env('GS_SECURE_HOST','http://10.0.2.2:7200'),
            'repository'=> env('GS_SECURE_REPOSITORY','secure'),
            'username'=> env('GS_SECURE_USERNAME','admin'),
            'password'=> env('GS_SECURE_PASSWORD','root'),
        ]
    ],
    'reconciliation'=> [
        'driver'=> 'graphdb_lucene',
        'index'=> 'reconcilation',
    ],
    'storage'=>[
        'disk'=>'local',
        'directories'=>[
            'root'=>'ldog',
            'conversions'=>'conversions',
        ],
    ],
    'silk'=>[
        'queue_connection'=>env('SILK_QUEUE_CONNECTION','redis'),
        'queue_name'=>env('SILK_QUEUE_NAME','silk'),
        'path'=>'D:\Laravel Packages\Binaries\silk\silk-3.2.0\silk-tools\silk-singlemachine\target\scala-2.11\silk.jar',
    ],
    'cabinet'=>[
        'name'=>env('CABINET_NAME'),
        'description'=>env('CABINET_DESCRIPTION'),
        'logoUrl'=>env('CABINET_LOGO_URL'),
        'admin_name'=>env('CABINET_ADMIN_NAME'),
        'admin_description'=>env('CABINET_ADMIN_DESCRIPTION'),
        'admin_username'=>env('CABINET_ADMIN_USERNAME'),
        'admin_password'=>env('CABINET_ADMIN_PASSWORD'),
    ],
];