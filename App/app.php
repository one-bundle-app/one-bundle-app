<?php

namespace OneBundleApp\App;

use Symfony\Component\HttpFoundation\Request;
use Mmoreram\BaseBundle\Kernel\BaseKernel;

$appPath = __DIR__ . '/../../..//';
$environment = 'prod';
require __DIR__ . '/autoload.php';

if (PHP_VERSION_ID < 70000) {
    include_once $appPath . '/var/bootstrap.php.cache';
}

$oneBundleAppConfig = new OneBundleAppConfig($appPath, $environment);
$kernel = new BaseKernel(
    $oneBundleAppConfig->getBundles(),
    $oneBundleAppConfig->getConfig(),
    [], 'prod', false,
    $appPath . '/var'
);

if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);