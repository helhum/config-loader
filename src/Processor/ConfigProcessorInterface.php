<?php
namespace Helhum\ConfigLoader\Processor;

/*
 * This file is part of the helhum configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface ConfigProcessorInterface
{
    /**
     * @param array $config
     * @return array
     */
    public function processConfig(array $config);
}
