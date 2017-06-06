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

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlFileReader implements ConfigReaderInterface
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
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @return array
     */
    public function readConfig()
    {
        try {
            return Yaml::parse(file_get_contents($this->configFile));
        } catch (ParseException $e) {
            throw new ParseException(sprintf('Error while parsing file "%s", Message: "%s"', $this->configFile, $e->getMessage()), 1496471748, $e);
        }
    }
}
