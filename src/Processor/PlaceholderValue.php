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

    private function isPlaceHolder($value)
    {
        return is_string($value) && preg_match(self::PLACEHOLDER_PATTERN, $value);
    }

    private function replacePlaceHolder($value)
    {
        if (!$this->isPlaceHolder($value)) {
            return $value;
        }
        preg_match(self::PLACEHOLDER_PATTERN, $value, $matches);
        switch ($matches[1]) {
            case 'env':
                $replacedValue = getenv($matches[2]);
                break;
            case 'const':
                $replacedValue = constant($matches[2]);
                break;
            case 'conf':
                $configPath = $matches[2];
                if (isset($this->currentlyReplacingConfPaths[$configPath])) {
                    throw new InvalidConfigurationFileException(sprintf('Recursion detected for config path "%s"', $configPath), 1519593176);
                }
                $this->currentlyReplacingConfPaths[$configPath] = true;
                $replacedValue = Config::getValue($this->referenceConfig, $configPath);
                if (is_array($replacedValue)) {
                    $replacedValue = $this->processConfig($replacedValue);
                } elseif ($this->isPlaceHolder($replacedValue)) {
                    $replacedValue = $this->replacePlaceHolder($replacedValue);
                }
                unset($this->currentlyReplacingConfPaths[$configPath]);
                break;
            case 'global':
                $replacedValue = Config::getValue($GLOBALS, $matches[2]);
                break;
            default:
                $replacedValue = $matches[0];
        }
        if ($value === $matches[0]) {
            // Direct match, replace as is
            return $replacedValue;
        }
        // Replace match inside string
        return preg_replace(self::PLACEHOLDER_PATTERN, $replacedValue, $value);
    }
}
