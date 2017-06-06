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

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlFileReader implements ConfigReaderInterface
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
        try {
            return Yaml::parse(file_get_contents($this->configFile));
        } catch (ParseException $e) {
            throw new ParseException(sprintf('Error while parsing file "%s", Message: "%s"', $this->configFile, $e->getMessage()), 1496471748, $e);
        }
    }
}
