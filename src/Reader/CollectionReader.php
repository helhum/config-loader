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

use Helhum\ConfigLoader\ConfigurationLoader;

class CollectionReader implements ConfigReaderInterface
{
    /**
     * @var ConfigReaderInterface[]
     */
    private $readers;

    /**
     * @param ConfigReaderInterface[] $readers
     */
    public function __construct(array $readers)
    {
        $this->readers = $readers;
    }

    /**
     * @return bool
     */
    public function hasConfig()
    {
        return !empty($this->readers);
    }

    /**
     * @throws \Helhum\ConfigLoader\InvalidConfigurationFileException
     * @return array
     */
    public function readConfig()
    {
        $configLoader = new ConfigurationLoader($this->readers);
        return $configLoader->load();
    }
}
