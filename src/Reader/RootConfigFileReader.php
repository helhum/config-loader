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

use Helhum\ConfigLoader\ConfigurationReaderFactory;
use Helhum\ConfigLoader\InvalidArgumentException;

class RootConfigFileReader implements ConfigReaderInterface
{
    /**
     * @var string
     */
    private $resourceFile;

    /**
     * @var ConfigurationReaderFactory
     */
    private $factory;

    /**
     * @var ConfigReaderInterface
     */
    private $reader;

    private static $currentlyImporting = [];

    public function __construct(string $resourceFile, array $options = [], ConfigurationReaderFactory $factory = null)
    {
        $this->resourceFile = $resourceFile;
        $this->factory = $factory ?: new ConfigurationReaderFactory();
        $this->reader = $this->factory->createReader($resourceFile, $options);
    }

    public function hasConfig(): bool
    {
        return $this->reader->hasConfig();
    }

    public function readConfig(): array
    {
        return $this->processImports($this->reader->readConfig());
    }

    /**
     * @param array $config
     * @throws InvalidArgumentException
     * @return array
     */
    private function processImports(array $config): array
    {
        if (!isset($config['imports'])) {
            return $config;
        }
        if (!is_array($config['imports'])) {
            throw new InvalidArgumentException(sprintf('The "imports" key should contain an array in "%s"', $this->resourceFile), 1496583179);
        }
        if (isset(self::$currentlyImporting[$this->resourceFile])) {
            throw new InvalidArgumentException('Recursion while importing ' . $this->resourceFile, 1496783180);
        }
        self::$currentlyImporting[$this->resourceFile] = true;
        $importedConfig = [];
        $importReaderFactory = $this->factory->withResourceBasePath(dirname($this->resourceFile));
        foreach ($config['imports'] as $import) {
            if (!is_array($import)) {
                throw new InvalidArgumentException(sprintf('The "imports" must be an array in "%s"', $this->resourceFile), 1496583180);
            }
            $reader = $importReaderFactory->createRootReader($import['resource'], $import);
            $ignoreErrors = $import['ignore_errors'] ?? false;
            if (!$reader->hasConfig()) {
                if ($ignoreErrors) {
                    continue;
                }
                throw new InvalidArgumentException(sprintf('Could not import mandatory resource "%s" in "%s"', $import['resource'], $this->resourceFile), 1496585828);
            }
            $importedConfig = array_replace_recursive($importedConfig, $reader->readConfig());
        }
        unset($config['imports'], self::$currentlyImporting[$this->resourceFile]);
        return array_replace_recursive($importedConfig, $config);
    }
}
