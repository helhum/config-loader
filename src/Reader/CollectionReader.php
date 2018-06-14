<?php
declare(strict_types=1);
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
    public function __construct(ConfigReaderInterface ...$readers)
    {
        $this->readers = $readers;
    }

    public function hasConfig(): bool
    {
        return !empty($this->readers);
    }

    public function readConfig(): array
    {
        $configLoader = new ConfigurationLoader($this->readers);

        return $configLoader->load();
    }
}
