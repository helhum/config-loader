<?php
namespace Helhum\ConfigLoader;

/*
 * This file is part of the helhum configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class CachedConfigurationLoader
 */
class CachedConfigurationLoader
{
    const CACHE_FILE_PATTERN = '/cached-configuration-%s.php';

    /**
     * @var string
     */
    private $cacheDir;
    /**
     * @var string
     */
    private $cacheIdentifier;
    /**
     * @var \Closure
     */
    private $configurationLoaderBuilder;
    /**
     * @var array
     */
    private $baseConfiguration;

    /**
     * @var string
     */
    private $cacheFileName;

    /**
     * CachedConfigurationLoader constructor.
     *
     * @param string $cacheDir
     * @param string $cacheIdentifier
     * @param \Closure $configurationLoaderBuilder
     */
    public function __construct($cacheDir, $cacheIdentifier, \Closure $configurationLoaderBuilder)
    {
        $this->cacheDir = $cacheDir;
        $this->cacheIdentifier = $cacheIdentifier;
        $this->configurationLoaderBuilder = $configurationLoaderBuilder;
    }

    /**
     * @return array
     * @throws InvalidConfigurationFileException
     */
    public function load()
    {
        if ($this->hasCache()) {
            return $this->loadCache();
        }
        /** @var ConfigurationLoader $configurationLoader */
        $configurationLoader = call_user_func($this->configurationLoaderBuilder);
        $finalConfiguration = $configurationLoader->load();
        if (!empty($this->cacheIdentifier)) {
            $this->cleanCache();
            $this->storeCache($finalConfiguration);
        }
        return $finalConfiguration;
    }

    protected function hasCache()
    {
        return @file_exists($this->getCacheFileName());
    }

    protected function loadCache()
    {
        require $this->getCacheFileName();
        $cacheClass = 'Helhum\\ConfigLoader\\CachedConfig' . $this->cacheIdentifier;
        return $cacheClass::$config;
    }

    protected function getCacheFileName()
    {
        if (empty($this->cacheFileName)) {
            $this->cacheFileName = $this->cacheDir . sprintf(self::CACHE_FILE_PATTERN, $this->cacheIdentifier);
        }
        return $this->cacheFileName;
    }

    protected function storeCache(array $finalConfiguration)
    {
        $configString = var_export($finalConfiguration, true);
        $content = <<<EOF
<?php
namespace Helhum\ConfigLoader;

class CachedConfig{$this->cacheIdentifier} {
    public static \$config = $configString;
}
EOF;
        return file_put_contents($this->getCacheFileName(), $content);
    }

    protected function cleanCache()
    {
        foreach (glob($this->getCacheFileGlob()) as $file) {
            @unlink($file);
        }
    }

    protected function getCacheFileGlob()
    {
        return $this->cacheDir . sprintf(self::CACHE_FILE_PATTERN, '*');
    }
}
