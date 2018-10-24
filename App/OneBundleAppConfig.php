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

use LogicException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class OneBundleAppConfig.
 */
class OneBundleAppConfig
{
    /**
     * @var array
     *
     * Bundles
     */
    private $bundles;

    /**
     * @var array
     *
     * Config
     */
    private $config;

    /**
     * @var array
     *
     * Routes
     */
    private $routes;

    /**
     * Constructor.
     *
     * @param string $appPath
     * @param string $environment
     */
    public function __construct(string $appPath, string $environment)
    {
        $configFromYml = array_replace_recursive(
            $this->loadConfigDataFromYmlFilepath("$appPath/app.yml"),
            $this->loadConfigDataFromYmlFilepath("$appPath/app_$environment.yml")
        );

        if (!isset($configFromYml['bundles'])) {
            throw new LogicException("Make sure that your app.yml or app_$environment.yml contains the bundles you want to execute");
        }

        $bundles = $configFromYml['bundles'];
        $this->bundles = is_array($bundles) ? $bundles : [$bundles];
        $this->config = $configFromYml['config'] ?? [];
        $this->routes = $configFromYml['routes'] ?? [];
    }

    /**
     * Load array from yml folder.
     *
     * return empty array of any problem
     */
    private function loadConfigDataFromYmlFilepath(string $ymlFilepath)
    {
        if (!is_file($ymlFilepath)) {
            return [];
        }

        return Yaml::parse(file_get_contents($ymlFilepath));
    }

    /**
     * Get Bundles.
     *
     * @return array
     */
    public function getBundles(): array
    {
        return $this->bundles;
    }

    /**
     * Get Config.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get Routes.
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
