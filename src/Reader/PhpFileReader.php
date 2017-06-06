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

class PhpFileReader implements ConfigReaderInterface
{
    /**
     * @var string
     */
    private $configFile;

    public function __construct(string $configFile)
    {
        $this->configFile = $configFile;
    }

    public function hasConfig(): bool
    {
        return file_exists($this->configFile);
    }

    public function readConfig(): array
    {
        return include $this->configFile;
    }
}
