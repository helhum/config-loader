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
class PhpFileReader implements ConfigReaderInterface
{
    /**
     * @var string
     */
    private $configDir;

    /**
     * PhpFileReader constructor.
     *
     * @param string $configDir
     */
    public function __construct($configDir)
    {
        $this->configDir = $configDir;
    }

    /**
     * @param string $configName
     * @return bool
     */
    public function hasConfig($configName)
    {
        return file_exists("{$this->configDir}/$configName.php");
    }

    /**
     * @param string $configName
     * @return array
     */
    public function readConfig($configName)
    {
        return include "{$this->configDir}/$configName.php";
    }
}
