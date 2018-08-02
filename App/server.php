#!/usr/bin/env php
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

/**
 * Errors to Exceptions
 */
function error_to_exception($code, $message, $file, $line, $context) {
    throw new \Exception($message, $code);
}
set_error_handler( 'error_to_exception',  E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_USER_DEPRECATED);

$environment = in_array('--dev', $argv) ? 'dev' : 'prod';
$appPath = __DIR__ . '/..';
$silent = in_array('--silent', $argv);
$debug = in_array('--debug', $argv);

require __DIR__ . '/../vendor/one-bundle-app/one-bundle-app/App/autoload.php';
use Symfony\Component\Dotenv\Dotenv;

$envPath = $appPath . '/.env';
if (file_exists($envPath)) {
    $dotenv = new Dotenv();
    $dotenv->load($envPath);
}

\Symfony\Component\Debug\ErrorHandler::register();
\Symfony\Component\Debug\ExceptionHandler::register();

$oneBundleAppConfig = new \OneBundleApp\App\OneBundleAppConfig($appPath, $environment);
$kernel = new \Mmoreram\BaseBundle\Kernel\BaseKernel(
    $oneBundleAppConfig->getBundles(),
    $oneBundleAppConfig->getConfig(),
    $oneBundleAppConfig->getRoutes(),
    $environment,
    $debug,
    $appPath . '/var'
);

/**
 * REACT SERVER
 */
$loop = \React\EventLoop\Factory::create();
$socket = new \React\Socket\Server($argv[1], $loop);


$http = new \React\Http\Server(function (\Psr\Http\Message\ServerRequestInterface $request) use ($kernel, $silent) {
    return new \React\Promise\Promise(function ($resolve, $reject) use ($request, $kernel, $silent) {

        $body = '';
        $request->getBody()->on('data', function ($data) use (&$body) {
            $body .= $data;
        });

        $request->getBody()->on('end', function () use ($resolve, &$body, $request, $kernel, $silent){

            try {
                $from = microtime(true);
                $method = $request->getMethod();
                $headers = $request->getHeaders();
                $query = $request->getQueryParams();
                $post = array();
                if (!empty($body)) {
                    parse_str($body, $post);
                    $post = is_array($post)
                        ? $post
                        : [];
                }

                $symfonyRequest = new \Symfony\Component\HttpFoundation\Request(
                    $query,
                    $post,
                    $request->getAttributes(),
                    $request->getCookieParams(),
                    $request->getUploadedFiles(),
                    array(), // Server is partially filled a few lines below
                    $body
                );

                $symfonyRequest->setMethod($method);
                $symfonyRequest->headers->replace($headers);
                $symfonyRequest->server->set('REQUEST_URI', $request->getUri());
                if (isset($headers['Host'])) {
                    $symfonyRequest->server->set('SERVER_NAME', explode(':', $headers['Host'][0]));
                }

                $symfonyResponse = $kernel->handle($symfonyRequest);
                $kernel->terminate($symfonyRequest, $symfonyResponse);
                $httpResponse = new \React\Http\Response(
                    $symfonyResponse->getStatusCode(),
                    $symfonyResponse->headers->all(),
                    $symfonyResponse->getContent()
                );
                $to = microtime(true);
                if (!$silent) {
                    echoRequestLine(
                        $request->getUri()->getPath(),
                        $method,
                        $symfonyResponse->getStatusCode(),
                        ($to - $from) * 1000
                    );
                }

                $symfonyRequest = null;
                $symfonyResponse = null;

                /**
                 * Catching errors and sending to syslog
                 */
            } catch (\Exception $e) {

                echoException($e);
                throw $e;
            }

            $resolve($httpResponse);
        });

        $request->getBody()->on('error', function (Exception $e) use ($resolve){
            echoException($e);
            $response = new \React\Http\Response(
                400,
                array('Content-Type' => 'text/plain'),
                "An error occured while reading from stream"
            );
            $resolve($response);
        });
    });
});

$http->on('error', function(\Exception $e) {
    echoException($e);
});

$http->listen($socket);
$loop->run();



/**
 * Common functions
 */

/**
 * Send to syslog
 *
 * @param \Exception $e
 */
function echoException(\Exception $e)
{
    echoLine("[{$e->getFile()}] [{$e->getCode()}] ::: [{$e->getMessage()}]");
}

/**
 * Echo request line
 *
 * @param string $url
 * @param string $method
 * @param int $code
 * @param int $elapsedTime
 */
function echoRequestLine(
    string $url,
    string $method,
    int $code,
    int $elapsedTime
) {
    $method = str_pad($method, 6, ' ');
    echo $code === 200
        ? "\033[01;32m".$code."\033[0m"
        : "\033[01;31m".$code."\033[0m";
    echo " $method $url ";
    echo "(\e[00;37m".$elapsedTime." ms\e[0m)" . PHP_EOL;
}

/**
 * Send to syslog
 *
 * @param string $line
 */
function echoLine(string $line) {
    echo($line) . PHP_EOL;
}
