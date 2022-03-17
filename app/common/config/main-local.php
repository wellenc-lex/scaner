<?php

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => getenv('DB_DSN', 'mysql:host=db;dbname=scaner'),
            'username' => getenv('DB_USER', 'web'),
            'password' => getenv('DB_PASSWORD', 'web'),
            'charset' => 'utf8mb4',
            'tablePrefix' => '',
            'attributes' => [
                PDO::ATTR_TIMEOUT => 100,
            ],
        ],

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => getenv('SMTP_HOST'),
                'username' => getenv('SMTP_USER'),
                'password' => getenv('SMTP_PASSWORD'),
                'port' => getenv('SMTP_PORT', 25),
                'encryption' => getenv('SMTP_ENCRYPTION', null),
            ],
        ],

        'request' => [
            'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY', "SUPERSECRETCOOKIEkeyll"),
            'trustedHosts' => explode(',', getenv('PROXY_HOST', '192.168.0.0/24')),
        ],
    ],
];

