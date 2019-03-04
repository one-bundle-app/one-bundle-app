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
$requestHandler = new \OneBundleApp\App\RequestHandler($kernel);

$http = new \React\Http\Server(function (\Psr\Http\Message\ServerRequestInterface $request) use ($requestHandler, $silent) {
    return new \React\Promise\Promise(function ($resolve, $reject) use ($request, $requestHandler, $silent) {
        list($httpResponse, $messages) = $requestHandler->handleServerRequest($request);

        if ($silent) {
            foreach ($messages as $message) {
                $message->print();
            }
        }

        $resolve($httpResponse);
    });
});

$http->on('error', function (\Throwable $e) {
    echoException($e);
});

$http->listen($socket);
$loop->run();
