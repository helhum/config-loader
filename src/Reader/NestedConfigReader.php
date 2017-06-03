<?php
namespace Helhum\ConfigLoader\Reader;

/*
 * This file is part of the helhum configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class NestedConfigReader implements ConfigReaderInterface
{
    /**
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * @var string
     */
    private $configPath;

    /**
     * @param ConfigReaderInterface $configReader
     * @param string $configPath
     * @internal param string $directory
     * @internal param string $fileExtension
     */
    public function __construct(ConfigReaderInterface $configReader, $configPath)
    {
        $this->configReader = $configReader;
        $this->configPath = $configPath;
    }

    public function hasConfig()
    {
        return $this->configReader->hasConfig();
    }

    public function readConfig()
    {
        return $this->setValue(array(), $this->configPath, $this->configReader->readConfig());
    }

    private function setValue(array $array, $configPath, $value)
    {
        if (!is_string($configPath) || $configPath === '') {
            throw new \RuntimeException('Path must be not be empty string', 1496472912);
        }
        // Extract parts of the configPath
        $configPath = str_getcsv($configPath, '.');
        // Point to the root of the array
        $pointer = &$array;
        // Find configPath in given array
        foreach ($configPath as $segment) {
            // Fail if the part is empty
            if ($segment === '') {
                throw new \RuntimeException('Invalid path segment specified', 1496472917);
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
