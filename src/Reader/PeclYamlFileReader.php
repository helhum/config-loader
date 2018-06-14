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

use Helhum\ConfigLoader\InvalidConfigurationFileException;

/**
 * Yaml file reader based on pecl PHP extension
 */
class PeclYamlFileReader implements ConfigReaderInterface
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
        $config = null;

        set_error_handler(function ($_, $errorMessage) {
            throw new InvalidConfigurationFileException($errorMessage, 1518628276);
        });
        try {
            $config = yaml_parse_file($this->configFile);
        } catch (InvalidConfigurationFileException $e) {
            throw new InvalidConfigurationFileException(sprintf('Error while parsing file "%s", Message: "%s"', $this->configFile, $e->getMessage()), 1518629212, $e);
        } finally {
            restore_error_handler();
        }

        if (!is_array($config)) {
            throw new InvalidConfigurationFileException(sprintf('Configuration file "%s" is invalid. It must return an array, but returned "%s"', $this->configFile, gettype($config)), 1518627396);
        }

        return $config;
    }
}
