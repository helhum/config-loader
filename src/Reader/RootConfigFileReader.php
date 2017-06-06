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

use Helhum\ConfigLoader\InvalidArgumentException;

class RootConfigFileReader implements ConfigReaderInterface
{
    /**
     * @var ConfigReaderInterface
     */
    private $reader;

    /**
     * @var string
     */
    private $resourceFile;

    public function __construct(string $resourceFile, string $type = null)
    {
        $this->resourceFile = $resourceFile;
        $this->reader = $this->createReader($resourceFile, $type);
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
        $importedConfig = [];
        foreach ($config['imports'] as $import) {
            if (!is_array($import)) {
                throw new InvalidArgumentException(sprintf('The "imports" must be an array in "%s"', $this->resourceFile), 1496583180);
            }
            $reader = $this->createProcessingReader($import['resource'], $import['type'] ?? null);
            $ignoreErrors = $import['type'] ?? false;
            if (!$ignoreErrors && !$reader->hasConfig()) {
                throw new InvalidArgumentException(sprintf('Could not import mandatory resource "%s" in "%s"', $import['resource'], $this->resourceFile), 1496585828);
            }
            $importedConfig = array_replace_recursive($importedConfig, $reader->readConfig());
        }
        unset($config['imports']);
        return array_replace_recursive($config, $importedConfig);
    }

    private function createProcessingReader(string $resource, string $type = null): ConfigReaderInterface
    {
        if ($type !== 'env') {
            $resource = $this->makeAbsolute($resource);
        }
        return new self($resource, $type);
    }

    private function createReader(string $resource, string $type = null): ConfigReaderInterface
    {
        $type = $type ?: pathinfo($resource, PATHINFO_EXTENSION);
        switch ($type) {
            case 'yml':
            case 'yaml':
                return new YamlFileReader($resource);
            case 'env':
                return new EnvironmentReader($resource);
            case 'glob':
                return new CollectionReader($this->createReaderCollection($resource));
            default:
                return new PhpFileReader($resource);
        }
    }

    /**
     * @param string $resource
     * @param string|null $type
     * @return ConfigReaderInterface[]
     */
    private function createReaderCollection(string $resource, string $type = null): array
    {
        $readers = [];
        $configFiles = glob($resource);
        foreach ($configFiles as $settingsFile) {
            $readers[] = $this->createProcessingReader($settingsFile, $type);
        }
        return $readers;
    }

    private function makeAbsolute(string $path): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }
        return dirname($this->resourceFile) . '/' . $path;
    }

    private function isAbsolutePath(string $path): bool
    {
        return $path[0] === '/' || $path[1] === ':';
    }
}
