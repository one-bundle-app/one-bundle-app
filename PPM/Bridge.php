<?php
/*
 * This file is part of the {Package name}.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

namespace OneBundleApp\PPM;

use OneBundleApp\App\RequestHandler;
use PHPPM\Bridges\BridgeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Bridge.
 */
class Bridge implements BridgeInterface
{
    /**
     * @var RequestHandler
     *
     * Request handler
     */
    private $requestHandler;

    /**
     * Bootstrap an application.
     *
     * @param string|null $appBootstrap
     * @param string      $appenv
     * @param bool        $debug
     */
    public function bootstrap($appBootstrap, $appenv, $debug)
    {
        $bootstrap = (new $appBootstrap());
        $bootstrap->initialize($appenv, $debug);
        $kernel = $bootstrap->getApplication();
        $this->requestHandler = new RequestHandler($kernel);
    }

    /**
     * Handle the request and return a response.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request)
    {
        list($httpResponse, $_) = $this
            ->requestHandler
            ->handleServerRequest($request);

        return $httpResponse;
    }
}
