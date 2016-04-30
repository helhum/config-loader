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

/**
 * Interface ConfigReaderInterface
 */
interface ConfigReaderInterface
{
    /**
     * @param string $configName
     * @return bool
     */
    public function hasConfig($configName);

    /**
     * @param string $configName
     * @return array
     */
    public function readConfig($configName);
}
