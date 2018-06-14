<?php
declare(strict_types=1);
namespace Helhum\ConfigLoader\Processor;

/*
 * This file is part of the helhum configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Helhum\ConfigLoader\Config;
use Helhum\ConfigLoader\InvalidConfigurationFileException;
use Helhum\ConfigLoader\PathDoesNotExistException;

class PlaceholderValue implements ConfigProcessorInterface
{
    const PLACEHOLDER_PATTERN = '/%(env|const|conf|global)\(([^)]+)\)%/';

    /**
     * @var array
     */
    private $referenceConfig;

    /**
     * @var array
     */
    private $currentlyReplacingConfPaths = [];

    /**
     * Strict processing means that an exception is thrown when replacement cannot be done.
     * In non strict mode placeholder is not replaced
     *
     * @var bool
     */
    private $strict;

    public function __construct(bool $strict = true)
    {
        $this->strict = $strict;
    }

    /**
     * @param array $config
     * @throws \InvalidArgumentException
     * @return array
     */
    public function processConfig(array $config): array
    {
        if (null === $this->referenceConfig) {
            $this->referenceConfig = $config;
        }
        $processedConfig = [];
        foreach ($config as $name => $value) {
            if (is_array($value)) {
                $processedConfig[$this->replacePlaceHolder($name)] = $this->processConfig($value);
            } else {
                $processedConfig[$this->replacePlaceHolder($name)] = $this->replacePlaceHolder($value);
            }
        }

        return $processedConfig;
    }

    /**
     * @param array $config
     * @param array|null $types
     * @param array $accumulatedPlaceholders
     * @param string $path
     * @return array
     */
    public function findPlaceholders(array $config, array $types = null, array $accumulatedPlaceholders = [], string $path = ''): array
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                if ($placeholder = $this->extractPlaceHolder($key, $types)) {
                    $accumulatedPlaceholders[$placeholder['placeholder']]['paths'][] = [
                        'path' => $path,
                        'isKey' => true,
                        'isDirectMatch' => $placeholder['isDirectMatch'],
                    ];
                    unset($placeholder['isDirectMatch']);
                    $accumulatedPlaceholders[$placeholder['placeholder']]['placeholder'] = $placeholder;
                }
                $accumulatedPlaceholders = $this->findPlaceholders($value, $types, $accumulatedPlaceholders, $path ? $path . '."' . $key . '"' : '"' . $key . '"');
            } else {
                if ($placeholder = $this->extractPlaceHolder($key, $types)) {
                    $accumulatedPlaceholders[$placeholder['placeholder']]['paths'][] = [
                        'path' => $path,
                        'isKey' => true,
                        'isDirectMatch' => $placeholder['isDirectMatch'],
                    ];
                    unset($placeholder['isDirectMatch']);
                    $accumulatedPlaceholders[$placeholder['placeholder']]['placeholder'] = $placeholder;
                }
                if ($placeholder = $this->extractPlaceHolder($value, $types)) {
                    $accumulatedPlaceholders[$placeholder['placeholder']]['paths'][] = [
                        'path' => $path ? $path . '."' . $key . '"' : '"' . $key . '"',
                        'isKey' => false,
                        'isDirectMatch' => $placeholder['isDirectMatch'],
                    ];
                    unset($placeholder['isDirectMatch']);
                    $accumulatedPlaceholders[$placeholder['placeholder']]['placeholder'] = $placeholder;
                }
            }
        }

        return $accumulatedPlaceholders;
    }

    private function isPlaceHolder($value)
    {
        return is_string($value) && preg_match(self::PLACEHOLDER_PATTERN, $value);
    }

    private function replacePlaceHolder($value)
    {
        if (!$placeholder = $this->extractPlaceHolder($value)) {
            return $value;
        }
        $replacedValue = null;
        switch ($placeholder['type']) {
            case 'env':
                if (getenv($placeholder['accessor']) === false) {
                    if ($this->strict) {
                        throw new InvalidConfigurationFileException(sprintf('Could not replace placeholder "%s" (environment variable "%s" does not exist)', $placeholder['placeholder'], $placeholder['accessor']), 1519640359);
                    }
                    break;
                }
                $replacedValue = getenv($placeholder['accessor']);
                break;
            case 'const':
                if (!defined($placeholder['accessor'])) {
                    if ($this->strict) {
                        throw new InvalidConfigurationFileException(sprintf('Could not replace placeholder "%s" (constant "%s" does not exist)', $placeholder['placeholder'], $placeholder['accessor']), 1519640600);
                    }
                    break;
                }
                $replacedValue = constant($placeholder['accessor']);
                break;
            case 'conf':
                $configPath = $placeholder['accessor'];
                if (isset($this->currentlyReplacingConfPaths[$configPath])) {
                    throw new InvalidConfigurationFileException(sprintf('Recursion detected for config path "%s"', $configPath), 1519593176);
                }
                try {
                    $this->currentlyReplacingConfPaths[$configPath] = true;
                    $replacedValue = Config::getValue($this->referenceConfig, $configPath);
                    if (is_array($replacedValue)) {
                        $replacedValue = $this->processConfig($replacedValue);
                    } elseif ($this->isPlaceHolder($replacedValue)) {
                        $replacedValue = $this->replacePlaceHolder($replacedValue);
                    }
                } catch (PathDoesNotExistException $e) {
                    if ($this->strict) {
                        throw new InvalidConfigurationFileException(sprintf('Could not replace placeholder "%s" (configuration path "%s" does not exist)', $placeholder['placeholder'], $placeholder['accessor']), 1519640588);
                    }
                } finally {
                    unset($this->currentlyReplacingConfPaths[$configPath]);
                }
                break;
            case 'global':
                try {
                    $replacedValue = Config::getValue($GLOBALS, $placeholder['accessor']);
                } catch (PathDoesNotExistException $e) {
                    if ($this->strict) {
                        throw new InvalidConfigurationFileException(sprintf('Could not replace placeholder "%s" (global variable path "%s" does not exist)', $placeholder['placeholder'], $placeholder['accessor']), 1519640631);
                    }
                }
                break;
        }
        if ($placeholder['isDirectMatch']) {
            return $replacedValue;
        }
        // Replace match inside string
        return preg_replace(self::PLACEHOLDER_PATTERN, $replacedValue, $value);
    }

    private function extractPlaceHolder($value, array $types = null): array
    {
        if (!$this->isPlaceHolder($value)) {
            return [];
        }
        preg_match(self::PLACEHOLDER_PATTERN, $value, $matches);
        if ($types !== null && !in_array($matches[1], $types, true)) {
            return [];
        }

        return [
            'placeholder' => $matches[0],
            'type' => $matches[1],
            'accessor' => $matches[2],
            'isDirectMatch' => $matches[0] === $value,
        ];
    }
}
