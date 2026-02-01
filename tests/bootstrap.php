<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

exec("php bin/console doctrine:database:create --env=test --if-not-exists");
exec("php bin/console doctrine:migrations:migrate --no-interaction --env=test");
