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

use Helhum\ConfigLoader\Processor\Placeholder\ConfigurationPlaceholder;
use Helhum\ConfigLoader\Processor\Placeholder\ConstantPlaceholder;
use Helhum\ConfigLoader\Processor\Placeholder\EnvironmentPlaceholder;
use Helhum\ConfigLoader\Processor\Placeholder\GlobalsPlaceholder;
use Helhum\ConfigLoader\Processor\Placeholder\PhpExportablePlaceholderInterface;
use Helhum\ConfigLoader\Processor\Placeholder\PlaceholderCollection;
use Helhum\ConfigLoader\Processor\Placeholder\PlaceholderInterface;
use Helhum\ConfigLoader\Processor\Placeholder\PlaceholderMatcher;
use Helhum\ConfigLoader\Processor\PlaceholderValue;

class ConfigurationExporter
{
    /**
     * @var PlaceholderMatcher
     */
    private $placeholderMatcher;

    /**
     * @var PlaceholderCollection
     */
    private $placeHolders;

    public function __construct(PlaceholderCollection $placeHolders = null, PlaceholderMatcher $placeholderMatcher = null)
    {
        $this->placeHolders = $placeHolders ?? new PlaceholderCollection([
            new EnvironmentPlaceholder(),
            new ConstantPlaceholder(),
            new ConfigurationPlaceholder(),
            new GlobalsPlaceholder(),
        ]);
        $this->placeholderMatcher = $placeholderMatcher ?? new PlaceholderMatcher($this->placeHolders->supportedTypes());
    }

    /**
     * Returns a PHP representation of a value, including value with placeholder
     *
     * @param mixed $value
     * @param array $referenceConfig
     * @param int $level
     * @return string
     */
    public function exportPhpCode($value, array $referenceConfig = [], int $level = 0): string
    {
        if (is_array($value)) {
            if ($value === []) {
                $code = '[]';
            } else {
                $value = $this->replacePlaceHolders($value);
                $code = '[' . chr(10);
                $writeIndex = $this->isHashMap($value);
                foreach ($value as $key => $arrayValue) {
                    // Indention
                    $code .= str_repeat('    ', $level + 1);
                    if ($writeIndex) {
                        // Integer / string keys
                        $code .= is_int($key) ? $key . ' => ' : $this->getPhpCodeForPlaceholder($key, $referenceConfig) . ' => ';
                    }
                    $code .= $this->exportPhpCode($arrayValue, $referenceConfig, $level + 1);
                    $code .= ',' . chr(10);
                }
                $code .= str_repeat('    ', $level) . ']';
            }
        } elseif (is_int($value) || is_float($value)) {
            $code = (string)$value;
        } elseif ($value === null) {
            $code = 'null';
        } elseif (is_bool($value)) {
            $code = $value ? 'true' : 'false';
        } elseif (is_string($value)) {
            $code = $this->getPhpCodeForPlaceholder($value, $referenceConfig);
        } else {
            throw new \RuntimeException('Objects, closures and resources are not supported', 1519779656);
        }

        return $code;
    }

    private function isHashMap(array $array): bool
    {
        $expectedKeyIndex = 0;
        foreach ($array as $key => $value) {
            if ($key === $expectedKeyIndex) {
                $expectedKeyIndex++;
            } else {
                // Found a non integer or non consecutive key, so we can break here
                return true;
            }
        }

        return false;
    }

    private function replacePlaceHolders(array $config): array
    {
        $placeholderProcessor = new PlaceholderValue(false, $this->placeHolders->onlyStatic());

        return $placeholderProcessor->processConfig($config);
    }

    private function getPhpCodeForPlaceholder($value, array $referenceConfig): string
    {
        $phpCode = '\'' . $this->escapePhpValue($value) . '\'';

        if (!$this->placeholderMatcher->isPlaceHolder($value)) {
            return $phpCode;
        }
        $placeholderMatch = $this->placeholderMatcher->extractPlaceHolder($value);

        foreach ($this->placeHolders as $placeHolder) {
            if ($placeHolder->supports($placeholderMatch->getType())) {
                $phpCode = $placeHolder->representsPhpCode($placeholderMatch->getAccessor(), $referenceConfig);

                if ($placeholderMatch->isDirectMatch()) {
                    return $phpCode;
                }

                // Replace match inside string
                return '\'' . str_replace($placeholderMatch->getPlaceholder(), '\' . ' . $phpCode . ' . \'', $value) . '\'';
            }
        }

        return $phpCode;
    }

    private function escapePhpValue(string $value): string
    {
        return addcslashes($value, '\\\'');
    }
}
