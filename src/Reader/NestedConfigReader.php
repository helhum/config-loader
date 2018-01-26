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

use Helhum\ConfigLoader\Config;

class NestedConfigReader implements ConfigReaderInterface
{
    /**
     * @var ConfigReaderInterface
     */
    private $reader;

    /**
     * @var string
     */
    private $configPath;

    public function __construct(ConfigReaderInterface $reader, string $configPath)
    {
        $this->reader = $reader;
        $this->configPath = $configPath;
    }

    public function hasConfig(): bool
    {
        return $this->reader->hasConfig();
    }

    public function readConfig(): array
    {
        return Config::setValue([], $this->configPath, $this->reader->readConfig());
    }
}
