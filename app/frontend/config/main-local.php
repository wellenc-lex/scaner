<?php

$config = [
    'components' => [
        'request' => [
            'cookieValidationKey' => '8sbNp57IpMD2cGbCU2xSNacA1hIyOPjt',
        ],
    ],
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],
        'historySize' => 1000,
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1'],
    ];


}

return $config;
