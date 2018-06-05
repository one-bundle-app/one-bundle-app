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

/**
 * Class ComposerHook.
 */
class ComposerHook
{
    /**
     * Install one bundle app environment.
     */
    public static function installEnvironment()
    {
        self::installConsole();
        self::installReactServer();
        self::installWebServer();
    }

    /**
     * Install react server.
     */
    public static function installReactServer()
    {
        $appPath = __DIR__ . '/../../../..';
        self::createFolderIfNotExists("$appPath/bin");
        self::createSoftLink(
            __DIR__,
            'server.php',
            "$appPath/bin",
            'server'
        );
        @chmod("$appPath/bin/server", 0755);
    }

    /**
     * Install one bundle console.
     */
    public static function installConsole()
    {
        $appPath = __DIR__ . '/../../../..';
        self::createFolderIfNotExists("$appPath/bin");
        self::createSoftLink(
            __DIR__,
            'console.php',
            "$appPath/bin",
            'console'
        );
        @chmod("$appPath/bin/console", 0755);
    }

    /**
     * Install web server.
     */
    public static function installWebServer()
    {
        $appPath = __DIR__ . '/../../../..';
        self::createFolderIfNotExists("$appPath/web");
        self::createSoftLink(
            __DIR__,
            'app.php',
            "$appPath/web",
            'app.php'
        );
        self::createSoftLink(
            __DIR__,
            'app_dev.php',
            "$appPath/web",
            'app_dev.php'
        );
    }

    /**
     * Create folder if not exists.
     *
     * @param string $path
     */
    private static function createFolderIfNotExists(string $path)
    {
        if (false === @mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf("Unable to create the %s directory\n", $path));
        }
    }

    /**
     * Make a soft link of a file, from a folder, into a folder.
     *
     * @param string $from
     * @param string $fromFilename
     * @param string $to
     * @param string $toFilename
     */
    private static function createSoftLink(
        string $from,
        string $fromFilename,
        string $to,
        string $toFilename
    ) {
        if (file_exists("$to/$toFilename")) {
            @unlink("$to/$toFilename");
        }

        symlink(
            "$from/$fromFilename",
            "$to/$toFilename"
        );
    }
}
