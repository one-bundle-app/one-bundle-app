<?php

/*
 * This file is part of the Apisearch Server
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace OneBundleApp\PPM;

use Mmoreram\BaseBundle\Kernel\BaseKernel;
use OneBundleApp\App\AppFactory;
use PHPPM\Bootstraps\ApplicationEnvironmentAwareInterface;
use PHPPM\Bootstraps\BootstrapInterface;

/**
 * Class Adapter.
 */
class Adapter implements BootstrapInterface, ApplicationEnvironmentAwareInterface
{
    /**
     * @var string
     *
     * Environment
     */
    protected $environment;

    /**
     * @var bool
     *
     * Debug
     */
    protected $debug;

    /**
     * Instantiate the bootstrap, storing the $appenv.
     *
     * @param string $environment
     * @param bool   $debug
     */
    public function initialize($environment, $debug)
    {
        $this->environment = $environment;
        $this->debug = $debug;
    }

    /**
     * @return BaseKernel
     */
    public function getApplication()
    {
        $kernel = AppFactory::createApp(
            __DIR__.'/../../../..',
            $this->environment,
            $this->debug
        );

        $kernel->boot();

        return $kernel;
    }
}
