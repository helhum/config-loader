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

use Helhum\ConfigLoader\Reader\ConfigReaderInterface;

/**
 * Class ConfigurationLoader
 */
class ConfigurationLoader
{
    /**
     * @var ConfigReaderInterface[]
     */
    private $configReaders;

    /**
     * ConfigurationLoader constructor.
     *
     * @param ConfigReaderInterface[] $configReaders
     */
    public function __construct(array $configReaders)
    {
        // todo validate instances
        $this->configReaders = $configReaders;
    }

    /**
     * @return array
     * @throws InvalidConfigurationFileException
     */
    public function load()
    {
        $finalConfig = array();
        foreach ($this->configReaders as $reader) {
            if ($reader->hasConfig()) {
                $readConfig = $reader->readConfig();
                if (!is_array($readConfig)) {
                    throw new InvalidConfigurationFileException('Configuration reader did not return an array!', 1462008832);
                }
                $finalConfig = array_replace_recursive($finalConfig, $readConfig);
            }
        }
        return $finalConfig;
    }
}
