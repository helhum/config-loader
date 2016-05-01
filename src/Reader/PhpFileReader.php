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
 * Class PhpFileReader
 */
class PhpFileReader implements ConfigReaderInterface
{
    /**
     * @var string
     */
    private $configFile;

    /**
     * PhpFileReader constructor.
     *
     * @param string $configFile
     */
    public function __construct($configFile)
    {
        $this->configFile = $configFile;
    }

    /**
     * @return bool
     */
    public function hasConfig()
    {
        return file_exists($this->configFile);
    }

    /**
     * @return array
     */
    public function readConfig()
    {
        return include $this->configFile;
    }
}
