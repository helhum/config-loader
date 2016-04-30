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
 * Class ConfigurationLoader
 */
class ConfigurationLoader
{
    /**
     * @var string
     */
    protected $applicationContext;

    /**
     * @var string
     */
    protected $configDir;

    /**
     * @var string
     */
    protected $envPrefix;

    /**
     * @var string
     */
    protected $envArraySeparator;

    /**
     * @var array A reference to the configuration array to override
     */
    protected $configuration;

    /**
     * ConfigurationLoader constructor.
     *
     * @param array $configuration
     * @param string $applicationContext
     * @param string $configDir
     * @param string $envPrefix
     * @param string $envArraySeparator
     */
    public function __construct(array &$configuration, $applicationContext, $configDir, $envPrefix = 'TYPO3', $envArraySeparator = '__')
    {
        $this->configuration = &$configuration;
        $this->applicationContext = $applicationContext;
        $this->configDir = $configDir;
        $this->envPrefix = $envPrefix;
        $this->envArraySeparator = $envArraySeparator;
    }

    public function load()
    {
        $configName = $this->getContextSlug();
        $configType = 'php';
        $this->readIfExists("{$this->configDir}/default.{$configType}");
        $this->readIfExists("{$this->configDir}/{$configName}.{$configType}");
        $this->loadConfigurationFromEnvironment();
        $this->readIfExists("{$this->configDir}/override.{$configType}");
    }

    protected function getContextSlug() {
        return strtr(strtolower($this->applicationContext), '/', '.');
    }

    /**
     * @param string $file
     * @throws InvalidConfigurationFileException
     */
    protected function readIfExists($file) {
        if (file_exists($file)) {
            $readConfig = include $file;
            if (!is_array($readConfig)) {
                throw new InvalidConfigurationFileException('Configuration file did not return an array!', 1462008832);
            }
            $this->configuration = array_replace_recursive($this->configuration, $readConfig);
        }
    }

    /**
     * Dynamically loads specific environment variables into the configiration
     * Env vars must start with $envPrefix and separate sections with $envArraySparator
     *
     * Example: TYPO3__DB__database will be loaded into $GLOBALS['TYPO3_CONF_VARS']['DB']['database']
     */
    protected function loadConfigurationFromEnvironment()
    {
        foreach ($_ENV as $name => $value) {
            if (!empty($this->envPrefix) && strpos($name, $this->envPrefix . $this->envArraySeparator) !== 0) {
                continue;
            }
            $this->configuration = self::setValueByPath(
                $this->configuration,
                str_replace($this->envArraySeparator, '/', substr($name, strlen($this->envPrefix . $this->envArraySeparator))),
                $value
            );
        }
    }

    /**
     * Shameless copy of \TYPO3\CMS\Core\Utility\ArrayUtility,
     * because requiring the whole typo3/cms package would be insane
     *
     * Modifies or sets a new value in an array by given path
     *
     * Example:
     * - array:
     * array(
     *   'foo' => array(
     *     'bar' => 42,
     *   ),
     * );
     * - path: foo/bar
     * - value: 23
     * - return:
     * array(
     *   'foo' => array(
     *     'bar' => 23,
     *   ),
     * );
     *
     * @param array $array Input array to manipulate
     * @param string $path Path in array to search for
     * @param mixed $value Value to set at path location in array
     * @param string $delimiter Path delimiter
     * @return array Modified array
     * @throws \RuntimeException
     */
    public static function setValueByPath(array $array, $path, $value, $delimiter = '/')
    {
        if (empty($path)) {
            throw new \RuntimeException('Path must not be empty. Please set a non empty $envPrefix!', 1462018404);
        }
        if (!is_string($path)) {
            throw new \RuntimeException('Path must be a string', 1341406402);
        }
        // Extract parts of the path
        $path = str_getcsv($path, $delimiter);
        // Point to the root of the array
        $pointer = &$array;
        // Find path in given array
        foreach ($path as $segment) {
            // Fail if the part is empty
            if (empty($segment)) {
                throw new \RuntimeException('Invalid path segment specified', 1341406846);
            }
            // Create cell if it doesn't exist
            if (!array_key_exists($segment, $pointer)) {
                $pointer[$segment] = array();
            }
            // Set pointer to new cell
            $pointer = &$pointer[$segment];
        }
        // Set value of target cell
        $pointer = $value;
        return $array;
    }
}
