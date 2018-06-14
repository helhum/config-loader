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

class Config
{
    /**
     * Getting a value to an array in a given path
     *
     * Inspired by \TYPO3\CMS\Core\Utility\ArrayUtility
     *
     * @param array $config
     * @param string $configPath Path separated by "."
     * @param mixed $default
     * @throws InvalidArgumentException
     * @throws PathDoesNotExistException
     * @return mixed
     */
    public static function getValue(array $config, string $configPath, $default = null)
    {
        if ($configPath === '') {
            throw new InvalidArgumentException('Path must be not be empty string', 1496758719);
        }
        $path = str_getcsv($configPath, '.');
        // Loop through each part and extract its value
        $value = $config;
        foreach ($path as $segment) {
            if (array_key_exists($segment, $value)) {
                // Replace current value with child
                $value = $value[$segment];
            } else {
                if (\count(\func_get_args()) !== 2) {
                    return $default;
                }
                // Fail if key does not exist and no default value is provided
                throw new PathDoesNotExistException(sprintf('Path "%s" does not exist in array (tried reading)', $configPath), 1496758722);
            }
        }

        return $value;
    }

    /**
     * Setting a value to an array in a given path
     *
     * Inspired by \TYPO3\CMS\Core\Utility\ArrayUtility
     *
     * @param array $array
     * @param string $configPath Path separated by "."
     * @param mixed $value
     * @throws InvalidArgumentException
     * @return array
     */
    public static function setValue(array $array, string $configPath, $value): array
    {
        if (!is_string($configPath) || $configPath === '') {
            throw new InvalidArgumentException('Path must be not be empty string', 1496472912);
        }
        // Extract parts of the configPath
        $path = str_getcsv($configPath, '.');
        // Point to the root of the array
        $pointer = &$array;
        // Find configPath in given array
        foreach ($path as $segment) {
            // Fail if the part is empty
            if ($segment === '') {
                throw new InvalidArgumentException('Invalid path segment specified', 1496472917);
            }
            // Create cell if it doesn't exist
            if (!array_key_exists($segment, $pointer)) {
                $pointer[$segment] = [];
            }
            // Set pointer to new cell
            $pointer = &$pointer[$segment];
        }
        // Set value of target cell
        $pointer = $value;

        return $array;
    }

    /**
     * Removing a path of an array
     *
     * Inspired by \TYPO3\CMS\Core\Utility\ArrayUtility
     *
     * @param array $config
     * @param string $configPath Path separated by "."
     * @throws InvalidArgumentException
     * @return array
     */
    public static function removeValue(array $config, string $configPath): array
    {
        if (!is_string($configPath) || $configPath === '') {
            throw new InvalidArgumentException('Path must be not be empty string', 1496759385);
        }
        // Extract parts of the path
        $path = str_getcsv($configPath, '.');
        $pathDepth = count($path);
        $currentDepth = 0;
        $pointer = &$config;
        // Find path in given array
        foreach ($path as $segment) {
            $currentDepth++;
            // Fail if the part is empty
            if ($segment === '') {
                throw new InvalidArgumentException('Invalid path segment specified', 1496759389);
            }
            if (!array_key_exists($segment, $pointer)) {
                throw new PathDoesNotExistException(sprintf('Path "%s" does not exist in array (tried removing)', $configPath), 1496759405);
            }
            if ($currentDepth === $pathDepth) {
                unset($pointer[$segment]);
            } else {
                $pointer = &$pointer[$segment];
            }
        }

        return $config;
    }
}
