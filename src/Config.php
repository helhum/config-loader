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
    public static function getValue(array $config, string $configPath)
    {
        if (!is_string($configPath) || $configPath === '') {
            throw new InvalidArgumentException('Path must be not be empty string', 1496758719);
        }
        $configPath = str_getcsv($configPath, '.');
        // Loop through each part and extract its value
        $value = $config;
        foreach ($configPath as $segment) {
            if (array_key_exists($segment, $value)) {
                // Replace current value with child
                $value = $value[$segment];
            } else {
                // Fail if key does not exist
                throw new InvalidArgumentException('Path does not exist in array', 1496758722);
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
     * @throws \Helhum\ConfigLoader\InvalidArgumentException
     * @return array
     */
    public static function setValue(array $array, string $configPath, $value): array
    {
        if (!is_string($configPath) || $configPath === '') {
            throw new InvalidArgumentException('Path must be not be empty string', 1496472912);
        }
        // Extract parts of the configPath
        $configPath = str_getcsv($configPath, '.');
        // Point to the root of the array
        $pointer = &$array;
        // Find configPath in given array
        foreach ($configPath as $segment) {
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
}
