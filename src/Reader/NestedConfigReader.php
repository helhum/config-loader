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

use Helhum\ConfigLoader\ArrayFill;

class NestedConfigReader implements ConfigReaderInterface
{
    /**
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * @var string
     */
    private $configPath;

    public function __construct(ConfigReaderInterface $configReader, string $configPath)
    {
        $this->configReader = $configReader;
        $this->configPath = $configPath;
    }

    public function hasConfig(): bool
    {
        return $this->configReader->hasConfig();
    }

    public function readConfig(): array
    {
        return ArrayFill::setValue([], $this->configPath, $this->configReader->readConfig());
    }
}
