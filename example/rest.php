<?php

require 'config.php';

$app = new Tonic\Application(array(
    'load' => __DIR__ . '/../server/Bazalt/Auth/Webservice/*.php'
));
$request = new Tonic\Request(array(
    'uri' => $_SERVER['PATH_INFO']
));

try {
    $resource = $app->getResource($request);
    $response = $resource->exec();
} catch (Tonic\NotFoundException $e) {
    $response = new Tonic\Response(404, $e->getMessage());
} catch (Tonic\Exception $e) {
echo $e->getMessage();
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
}
$response->output();
