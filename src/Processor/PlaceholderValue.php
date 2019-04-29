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
use Helhum\ConfigLoader\Processor\Placeholder\ConfigurationPlaceholder;
use Helhum\ConfigLoader\Processor\Placeholder\ConstantPlaceholder;
use Helhum\ConfigLoader\Processor\Placeholder\EnvironmentPlaceholder;
use Helhum\ConfigLoader\Processor\Placeholder\GlobalsPlaceholder;
use Helhum\ConfigLoader\Processor\Placeholder\PlaceholderCollection;
use Helhum\ConfigLoader\Processor\Placeholder\PlaceholderMatcher;

class PlaceholderValue implements ConfigProcessorInterface
{
    /**
     * @var array
     */
    private $referenceConfig;

    /**
     * @var array
     */
    private $currentlyReplacingPlaceholder = [];

    /**
     * Strict processing means that an exception is thrown when replacement cannot be done.
     * In non strict mode placeholder is not replaced
     *
     * @var bool
     */
    private $strict;

    /**
     * @var PlaceholderMatcher
     */
    private $placeholderMatcher;

    /**
     * @var PlaceholderCollection
     */
    private $placeHolders;

    public function __construct(bool $strict = true, PlaceholderCollection $placeHolders = null, PlaceholderMatcher $placeholderMatcher = null)
    {
        $this->strict = $strict;
        $this->placeHolders = $placeHolders ?? new PlaceholderCollection([
            new EnvironmentPlaceholder(),
            new ConstantPlaceholder(),
            new ConfigurationPlaceholder(),
            new GlobalsPlaceholder(),
        ]);
        $this->placeholderMatcher = $placeholderMatcher ?? new PlaceholderMatcher($this->placeHolders->supportedTypes());
    }

    /**
     * @param array $config
     * @throws \InvalidArgumentException
     * @return array
     */
    public function processConfig(array $config): array
    {
        if ($this->referenceConfig === null) {
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

    private function replacePlaceHolder($value)
    {
        if (!$this->placeholderMatcher->hasPlaceHolders($value)) {
            return $value;
        }

        $placeholderMatches = $this->placeholderMatcher->extractPlaceHolders($value);
        foreach ($placeholderMatches as $placeholderMatch) {
            $replacedValue = null;
            if (isset($this->currentlyReplacingPlaceholder[$placeholderMatch->getPlaceholder()])) {
                throw new InvalidConfigurationFileException(sprintf('Recursion detected for placeholder "%s"', $placeholderMatch->getPlaceholder()), 1519593176);
            }
            $this->currentlyReplacingPlaceholder[$placeholderMatch->getPlaceholder()] = true;
            $foundMatch = false;
            $supports = false;
            foreach ($this->placeHolders as $placeHolder) {
                $supports = $placeHolder->supports($placeholderMatch->getType());
                $canReplace = $placeHolder->canReplace($placeholderMatch->getAccessor(), $this->referenceConfig);
                $foundMatch = $supports && $canReplace;
                if ($foundMatch) {
                    $replacedValue = $this->cast(
                        $placeHolder->representsValue($placeholderMatch->getAccessor(), $this->referenceConfig),
                        $placeholderMatch->getDataType()
                    );

                    if (is_array($replacedValue)) {
                        $replacedValue = $this->processConfig($replacedValue);
                    } elseif ($this->placeholderMatcher->hasPlaceHolders($replacedValue)) {
                        $replacedValue = $this->replacePlaceHolder($replacedValue);
                    }
                }
                if ($supports) {
                    break;
                }
            }
            unset($this->currentlyReplacingPlaceholder[$placeholderMatch->getPlaceholder()]);

            if (!$foundMatch && $this->strict) {
                throw new InvalidConfigurationFileException(sprintf('Could not replace placeholder "%s"', $placeholderMatch->getPlaceholder()), 1519640359);
            }

            if ($placeholderMatch->isDirectMatch()) {
                return $replacedValue;
            }

            if ($supports || count($placeholderMatches) === 1) {
                // Replace match inside string
                $value = str_replace($placeholderMatch->getPlaceholder(), (string)$replacedValue, $value);
            }
        }

        return $value;
    }

    private function cast($value, string $dataType)
    {
        switch ($dataType) {
            case 'int':
                return (int)$value;
            case 'string':
                return (string)$value;
            case 'bool':
                return (bool)$value;
            case 'float':
                return (float)$value;
        }

        return $value;
    }
}
