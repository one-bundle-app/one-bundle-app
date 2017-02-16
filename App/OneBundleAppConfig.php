<?php

namespace OneBundleApp\App;

use Symfony\Component\Yaml\Yaml;
use LogicException;

/**
 * Class OneBundleAppConfig
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
     * Constructor
     *
     * @param string $appPath
     * @param string $environment
     */
    public function __construct(string $appPath, string $environment)
    {
        $configFromYml = array_merge(
            $this->loadConfigDataFromYmlFilepath("$appPath/app.yml"),
            $this->loadConfigDataFromYmlFilepath("$appPath/app_$environment.yml")
        );

        if (!isset($configFromYml['bundles'])) {
            throw new LogicException("Make sure that your app.yml or app_$environment.yml contains the bundles you want to execute");
        }

        $bundles = $configFromYml['bundles'];
        $this->bundles = is_array($bundles) ? $bundles : [$bundles];
        $this->config = $configFromYml['config'] ?? [];
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
     * Get Bundles
     *
     * @return array
     */
    public function getBundles() : array
    {
        return $this->bundles;
    }

    /**
     * Get Config
     *
     * @return array
     */
    public function getConfig() : array
    {
        return $this->config;
    }
}