<?php
declare(strict_types=1);
namespace Helhum\ConfigLoader;

/*
 * This file is part of the helhum configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CachedConfigurationLoader implements ConfigurationLoaderInterface
{
    const CACHE_FILE_PATTERN = '/cached-config-%s.php';

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
    public function __construct(string $cacheDir, string $cacheIdentifier, \Closure $configurationLoaderBuilder)
    {
        $this->cacheDir = $cacheDir;
        $this->cacheIdentifier = $cacheIdentifier;
        $this->configurationLoaderBuilder = $configurationLoaderBuilder;
        $this->cacheFileName = $this->cacheDir . sprintf(self::CACHE_FILE_PATTERN, $this->cacheIdentifier);
    }

    public function load(): array
    {
        if ($this->hasCache()) {
            return $this->loadCache();
        }
        /** @var ConfigurationLoader $configurationLoader */
        $configurationLoader = ($this->configurationLoaderBuilder)();
        $finalConfiguration = $configurationLoader->load();
        if (!empty($this->cacheIdentifier)) {
            $this->cleanCache();
            $this->storeCache($finalConfiguration);
        }

        return $finalConfiguration;
    }

    protected function hasCache(): bool
    {
        return file_exists($this->cacheFileName);
    }

    private function loadCache(): array
    {
        return include $this->cacheFileName;
    }

    private function storeCache(array $finalConfiguration)
    {
        $configString = var_export($finalConfiguration, true);
        $content = <<<EOF
<?php
return
$configString;

EOF;

        return file_put_contents($this->cacheFileName, $content);
    }

    private function cleanCache()
    {
        foreach (glob($this->getCacheFileGlob()) as $file) {
            @unlink($file);
        }
    }

    private function getCacheFileGlob(): string
    {
        return $this->cacheDir . sprintf(self::CACHE_FILE_PATTERN, '*');
    }
}
