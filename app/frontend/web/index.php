<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

ini_set("default_socket_timeout", 1000);
ini_set('display_errors','1');
ini_set('memory_limit', '-1');
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$factory = new Dotenv\Environment\DotenvFactory([
    new Dotenv\Environment\Adapter\EnvConstAdapter(),
    new Dotenv\Environment\Adapter\PutenvAdapter(),
]);

$dotenv = Dotenv\Dotenv::create("/var/www/app", null, $factory);
$dotenv->load();
$dotenv->required(['DB_DSN', 'DB_USER', 'DB_PASSWORD', 'COOKIE_VALIDATION_KEY']);

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);

(new yii\web\Application($config))->run();
