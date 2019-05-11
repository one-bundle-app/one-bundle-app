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

use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class AppFactory.
 */
class AppFactory
{
    /**
     * Create app.
     *
     * @param string $appPath
     * @param string $environment
     * @param bool   $debug
     * @param bool   $async
     *
     * @return BaseKernel
     */
    public static function createApp(
        string $appPath,
        string $environment,
        bool $debug,
        bool $async = false
    ): BaseKernel {
        $envPath = $appPath.'/.env';
        if (file_exists($envPath)) {
            $dotenv = new Dotenv();
            $dotenv->load($envPath);
        }

        $oneBundleAppConfig = new \OneBundleApp\App\OneBundleAppConfig($appPath, $environment);
        \Symfony\Component\Debug\ErrorHandler::register();
        \Symfony\Component\Debug\ExceptionHandler::register();

        $kernelClass = $async
            ? \Mmoreram\BaseBundle\Kernel\AsyncBaseKernel::class
            : \Mmoreram\BaseBundle\Kernel\BaseKernel::class;

        return new $kernelClass(
            $oneBundleAppConfig->getBundles(),
            $oneBundleAppConfig->getConfig(),
            $oneBundleAppConfig->getRoutes(),
            $environment,
            $debug,
            $appPath.'/var'
        );
    }
}
