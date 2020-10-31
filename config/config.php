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
];