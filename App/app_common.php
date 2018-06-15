<?php

/*
 * This file is part of the OneBundleApp package.
 *
 * Copyright (c) >=2017 Marc Morera
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace OneBundleApp\App;

use Dotenv\Dotenv;
use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Symfony\Component\HttpFoundation\Request;

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$oneBundleAppConfig = new OneBundleAppConfig($appPath, $environment);
$kernel = new BaseKernel(
    $oneBundleAppConfig->getBundles(),
    $oneBundleAppConfig->getConfig(),
    $oneBundleAppConfig->getRoutes(),
    $environment, $debug,
    $appPath . '/var'
);

if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
