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

use Helhum\ConfigLoader\Processor\ConfigProcessorInterface;
use Helhum\ConfigLoader\Reader\ConfigReaderInterface;

class ConfigurationLoader implements ConfigurationLoaderInterface
{
    /**
     * @var ConfigReaderInterface[]
     */
    private $configReaders;

    /**
     * @var ConfigProcessorInterface[]
     */
    private $configProcessors;

    /**
     * ConfigurationLoader constructor.
     *
     * @param ConfigReaderInterface[] $configReaders
     * @param ConfigProcessorInterface[] $configProcessors
     */
    public function __construct(array $configReaders, array $configProcessors = [])
    {
        array_walk($configReaders, [$this, 'ensureValidReader']);
        array_walk($configProcessors, [$this, 'ensureValidProcessor']);
        $this->configReaders = $configReaders;
        $this->configProcessors = $configProcessors;
    }

    /**
     * @throws InvalidConfigurationFileException
     * @return array
     */
    public function load(): array
    {
        $finalConfig = [];
        foreach ($this->configReaders as $i => $reader) {
            if ($reader->hasConfig()) {
                try {
                    $readConfig = $reader->readConfig();
                    $finalConfig = array_replace_recursive($finalConfig, $readConfig);
                } catch (\TypeError $e) {
                    throw new InvalidConfigurationFileException(sprintf(
                        'Configuration reader at index "%d" ("%s") did not return an array!',
                        $i,
                        get_class($reader)
                    ), 1462008832, $e);
                }
            }
        }
        foreach ($this->configProcessors as $configProcessor) {
            $finalConfig = $configProcessor->processConfig($finalConfig);
        }

        return $finalConfig;
    }

    private function ensureValidReader($potentialReader)
    {
        if (!$potentialReader instanceof ConfigReaderInterface) {
            throw new InvalidArgumentException('Reader does not implement ConfigReaderInterface', 1462067510);
        }
    }

    private function ensureValidProcessor($potentialProcessor)
    {
        if (!$potentialProcessor instanceof ConfigProcessorInterface) {
            throw new InvalidArgumentException('Proessor does not implement ConfigProcessorInterface', 1496409084);
        }
    }
}
