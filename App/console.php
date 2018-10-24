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

use OneBundleApp\App\AppFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;

set_time_limit(0);
$appPath = __DIR__.'/..';
require __DIR__.'/../vendor/one-bundle-app/one-bundle-app/App/autoload.php';

$input = new ArgvInput();
$environment = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'dev');
$debug = '0' !== getenv('SYMFONY_DEBUG') && !$input->hasParameterOption(['--no-debug', '']) && 'prod' !== $environment;

if ($debug) {
    Debug::enable();
}

$kernel = AppFactory::createApp(
    $appPath,
    $environment,
    $debug
);

$application = new Application($kernel);
$application->run($input);
