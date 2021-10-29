<?php
declare(strict_types=1);
namespace Helhum\ConfigLoader\Tests\Unit\Fixture;

/*
 * This file is part of the helhum TYPO3 configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Helhum\ConfigLoader\Reader\ConfigReaderInterface;

class ArrayReader implements ConfigReaderInterface
{
    public $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function hasConfig(): bool
    {
        return true;
    }

    public function readConfig(): array
    {
        return $this->config;
    }
}
