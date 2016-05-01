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

/**
 * Class EnvironmentReader
 */
class EnvironmentReader implements ConfigReaderInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $keySeparator;

    /**
     * PhpFileReader constructor.
     *
     * @param string $prefix
     * @param string $keySeparator
     */
    public function __construct($prefix, $keySeparator = '__')
    {
        $this->prefix = $prefix;
        $this->keySeparator = $keySeparator;
    }

    /**
     * @return bool
     */
    public function hasConfig()
    {
        // Looping would be similarly expensive as reading
        return true;
    }

    /**
     * @return array
     */
    public function readConfig()
    {
        $finalConfiguration = array();
        foreach ($_ENV as $name => $value) {
            if (!empty($this->prefix) && strpos($name, $this->prefix . $this->keySeparator) !== 0) {
                continue;
            }
            $finalConfiguration = self::setValueByPath(
                $finalConfiguration,
                str_replace($this->keySeparator, '/', substr($name, strlen($this->prefix . $this->keySeparator))),
                $value
            );
        }
        return $finalConfiguration;
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
