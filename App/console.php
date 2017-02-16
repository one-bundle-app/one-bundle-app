#!/usr/bin/env php
<?php

namespace OneBundleApp\App;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;
use Mmoreram\BaseBundle\Tests\BaseKernel;

set_time_limit(0);
$appPath = __DIR__ . '/../../..//';
$appPath = __DIR__ . '/../../MyBundle';
require __DIR__ . '/autoload.php';

$input = new ArgvInput();
$env = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'dev');
$debug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(['--no-debug', '']) && $env !== 'prod';

if ($debug) {
    Debug::enable();
}

$oneBundleAppConfig = new OneBundleAppConfig($appPath, $env);
$kernel = new BaseKernel(
    $oneBundleAppConfig->getBundles(),
    $oneBundleAppConfig->getConfig(),
    [], $env, $debug,
    $appPath . '/var'
);

$application = new Application($kernel);
$application->run($input);