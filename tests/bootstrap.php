<?php

namespace tests;

require_once (is_file(__DIR__ . '/../vendor/autoload.php') ? (__DIR__ . '/../vendor/autoload.php') : '../vendor/autoload.php');

$loader = new \Composer\Autoload\ClassLoader();
$loader->add('tests', __DIR__ . '/..');
$loader->register();

if (!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', getenv('APPLICATION_ENV'));
}
define('DEVELOPMENT_STAGE', APPLICATION_ENV == 'development');
define('PRODUCTION_STAGE',  APPLICATION_ENV == 'production');
define('TESTING_STAGE',     APPLICATION_ENV == 'testing');


$dbParams = array(
    'server' => $GLOBALS['db_host'],
    'username' => $GLOBALS['db_username'],
    'password' => $GLOBALS['db_password'],
    'database' => $GLOBALS['db_name'],
    'port' => $GLOBALS['db_port']
);

$connectionString = new \Bazalt\ORM\Adapter\Mysql($dbParams);
\Bazalt\ORM\Connection\Manager::add($connectionString);