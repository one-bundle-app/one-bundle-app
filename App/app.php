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

$appPath = __DIR__.'/..';
$environment = 'prod';
$debug = false;
require __DIR__.'/../vendor/one-bundle-app/one-bundle-app/App/autoload.php';
require __DIR__.'/../vendor/one-bundle-app/one-bundle-app/App/app_common.php';
