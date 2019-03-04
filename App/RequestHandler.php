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
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response as ReactResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestHandler.
 */
class RequestHandler
{
    /**
     * @var BaseKernel
     *
     * Kernel
     */
    private $kernel;

    /**
     * RequestHandler constructor.
     *
     * @param BaseKernel $kernel
     */
    public function __construct(BaseKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Handle server request and return response.
     *
     * Return an array of an instance of ResponseInterface and an array of
     * Printable instances
     *
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function handleServerRequest(ServerRequestInterface $request): array
    {
        $messages = [];

        try {
            $body = $request->getBody()->getContents();
            $uriPath = $request->getUri()->getPath();

            if ('/favicon.ico' === $uriPath) {
                return [$this->createFaviconResponse(), []];
            }

            $from = microtime(true);
            $method = $request->getMethod();
            $headers = $request->getHeaders();
            $query = $request->getQueryParams();
            $post = [];
            if (!empty($body)) {
                parse_str($body, $post);
                $post = is_array($post)
                    ? $post
                    : [];
            }

            $symfonyRequest = new Request(
                $query,
                $post,
                $request->getAttributes(),
                $request->getCookieParams(),
                $request->getUploadedFiles(),
                [], // Server is partially filled a few lines below
                $body
            );

            $symfonyRequest->setMethod($method);
            $symfonyRequest->headers->replace($headers);
            $symfonyRequest->server->set('REQUEST_URI', $uriPath);
            if (isset($headers['Host'])) {
                $symfonyRequest->server->set('SERVER_NAME', explode(':', $headers['Host'][0]));
            }

            $symfonyResponse = $this->kernel->handle($symfonyRequest);
            $this->kernel->terminate($symfonyRequest, $symfonyResponse);
            $httpResponse = new \React\Http\Response(
                $symfonyResponse->getStatusCode(),
                $symfonyResponse->headers->all(),
                $symfonyResponse->getContent()
            );
            $to = microtime(true);
            $messages[] = new ConsoleMessage(
                $request->getUri()->getPath(),
                $method,
                $symfonyResponse->getStatusCode(),
                $symfonyResponse->getContent(),
                \intval(($to - $from) * 1000)
            );

            $symfonyRequest = null;
            $symfonyResponse = null;

            /*
             * Catching errors and sending to syslog.
             */
        } catch (\Throwable $exception) {
            $messages[] = new ConsoleException($exception);
            $httpResponse = new \React\Http\Response(
                400,
                ['Content-Type' => 'text/plain'],
                $exception->getMessage()
            );
        }

        return [$httpResponse, $messages];
    }

    /**
     * Echo favicon.
     *
     * @return \React\Http\Response
     */
    private function createFaviconResponse()
    {
        return new ReactResponse(
            200,
            [
                'cache-control' => 'max-age=31556926, public, s-maxage=31556926',
                'access-control-allow-origin' => '*',
                'Content-Type' => 'image/ico',
                'Content-Length' => 0,
            ],
            ''
        );
    }
}
