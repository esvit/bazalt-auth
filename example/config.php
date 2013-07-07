<?php

require 'vendor/autoload.php';

use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

$run     = new Whoops\Run;
$handler = new PrettyPageHandler;
$run->pushHandler($handler);

$jsonHandler = new JsonResponseHandler();
$jsonHandler->onlyForAjaxRequests(true);
$run->pushHandler($jsonHandler);

$run->pushHandler(
    function ($exception, $inspector, $whoops) {

        // Set response code
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        // Log error
        /*
            $exception->getMessage(),
            array(
                'File' => $exception->getFile(),
                'Line' => $exception->getLine()
            )*/
    }
);
$run->register();


$connectionString = new \Bazalt\ORM\Adapter\Mysql([
    'server' => '127.0.0.1',
    'port' => '3306',
    'database' => 'bazalt_cms',
    'username' => 'root',
    'password' => 'awdawd'
]);
\Bazalt\ORM\Connection\Manager::add($connectionString, 'default');
