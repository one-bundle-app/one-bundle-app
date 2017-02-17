#!/usr/bin/env php
<?php

/*
 * This file is part of the OneBundleApp package.
 *
 * Copyright (c) >=2014 Marc Morera
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

use Mmoreram\BaseBundle\Tests\BaseKernel;
use OneBundleApp\App\OneBundleAppConfig;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;

set_time_limit(0);
$appPath = __DIR__ . '/../../../..';
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
    $oneBundleAppConfig->getRoutes(),
    $env,
    $debug,
    $appPath . '/var'
);

$application = new Application($kernel);
$application->run($input);
