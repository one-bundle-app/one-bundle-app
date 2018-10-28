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
 * Errors to Exceptions.
 */
function error_to_exception($code, $message, $file, $line, $context)
{
    throw new \Exception($message, $code);
}
set_error_handler('error_to_exception', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_USER_DEPRECATED);

$environment = in_array('--dev', $argv) ? 'dev' : 'prod';
$appPath = __DIR__.'/..';
$silent = in_array('--silent', $argv);
$debug = in_array('--debug', $argv);
$api = in_array('--api', $argv);

require __DIR__.'/../vendor/one-bundle-app/one-bundle-app/App/autoload.php';
$kernel = \OneBundleApp\App\AppFactory::createApp(
    $appPath,
    $environment,
    $debug
);

/**
 * REACT SERVER.
 */
$loop = \React\EventLoop\Factory::create();
$socket = new \React\Socket\Server($argv[1], $loop);

$http = new \React\Http\Server(function (\Psr\Http\Message\ServerRequestInterface $request) use ($kernel, $silent, $api) {
    return new \React\Promise\Promise(function ($resolve, $reject) use ($request, $kernel, $silent, $api) {
        try {
            $body = $request->getBody()->getContents();

            if ($api) {
                if ('/favicon.ico' === (string) $request->getUri()->getPath()) {
                    $resolve(createFaviconResponse());

                    return;
                }
            }

            try {
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

                $symfonyRequest = new \Symfony\Component\HttpFoundation\Request(
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
                        $symfonyResponse->getContent(),
                        ($to - $from) * 1000
                    );
                }

                $symfonyRequest = null;
                $symfonyResponse = null;

                /*
                 * Catching errors and sending to syslog.
                 */
            } catch (\Exception $exception) {
                echoException($exception);
                $httpResponse = new \React\Http\Response(
                    400,
                    ['Content-Type' => 'text/plain'],
                    $exception->getMessage()
                );
            }
        } catch (\RuntimeException $runtimeException) {
            echoException($runtimeException);
            $httpResponse = new \React\Http\Response(
                400,
                ['Content-Type' => 'text/plain'],
                'An error occured while reading from stream'
            );
        }

        $resolve($httpResponse);
    });
});

$http->on('error', function (\Exception $e) {
    echoException($e);
});

$http->listen($socket);
$loop->run();

/**
 * Common functions.
 */

/**
 * Send to syslog.
 *
 * @param \Exception $e
 */
function echoException(\Exception $e)
{
    echoLine("[{$e->getFile()}] [{$e->getCode()}] ::: [{$e->getMessage()}]");
}

/**
 * Echo request line.
 *
 * @param string $url
 * @param string $method
 * @param int    $code
 * @param string $message
 * @param int    $elapsedTime
 */
function echoRequestLine(
    string $url,
    string $method,
    int $code,
    string $message,
    int $elapsedTime
) {
    $method = str_pad($method, 6, ' ');
    $color = '32';
    if ($code >= 300 && $code < 400) {
        $color = '33';
    } elseif ($code >= 400) {
        $color = '31';
    }

    echo "\033[01;{$color}m".$code."\033[0m";
    echo " $method $url ";
    echo "(\e[00;37m".$elapsedTime.' ms | '.((int) (memory_get_usage() / 1000000))." MB\e[0m)";
    if ($code >= 300) {
        echo " - \e[00;37m".messageInMessage($message)."\e[0m";
    }
    echo PHP_EOL;
}

/**
 * Find message.
 *
 * @param string $message
 *
 * @return string
 */
function messageInMessage(string $message): string
{
    $decodedMessage = json_decode($message, true);
    if (
        is_array($decodedMessage) &&
        isset($decodedMessage['message']) &&
        is_string($decodedMessage['message'])
    ) {
        return $decodedMessage['message'];
    }

    return $message;
}

/**
 * Send to syslog.
 *
 * @param string $line
 */
function echoLine(string $line)
{
    echo($line).PHP_EOL;
}

/**
 * Echo favicon.
 *
 * @return \React\Http\Response
 */
function createFaviconResponse()
{
    return new \React\Http\Response(
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
