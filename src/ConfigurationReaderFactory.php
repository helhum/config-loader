<?php
declare(strict_types=1);
namespace Helhum\ConfigLoader;

/*
 * This file is part of the helhum configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Helhum\ConfigLoader\Reader\ClosureConfigReader;
use Helhum\ConfigLoader\Reader\ConfigReaderInterface;
use Helhum\ConfigLoader\Reader\EnvironmentReader;
use Helhum\ConfigLoader\Reader\GlobFileReader;
use Helhum\ConfigLoader\Reader\NestedConfigReader;
use Helhum\ConfigLoader\Reader\PhpFileReader;
use Helhum\ConfigLoader\Reader\RootConfigFileReader;
use Helhum\ConfigLoader\Reader\YamlFileReader;

class ConfigurationReaderFactory
{
    /**
     * @var string
     */
    private $resourceBasePath;

    private $readerTypes = [];

    private $addedReaderTypes = [];

    public function __construct(string $resourceBasePath = null)
    {
        $this->resourceBasePath = $resourceBasePath;
        $this->registerDefaultReaderTypes();
    }

    /**
     * @param mixed $readerFactory
     * @param string $type
     */
    public function setReaderFactoryForType(string $type, $readerFactory)
    {
        $this->addedReaderTypes[$type] = $readerFactory;
        $this->readerTypes[$type] = $readerFactory;
    }

    public function createReader(string $resource, array $options = []): ConfigReaderInterface
    {
        return $this->createDecoratedReader($resource, $options);
    }

    public function createRootReader(string $resource, array $options = []): ConfigReaderInterface
    {
        return $this->createDecoratedReader($resource, $options, 'rootReader');
    }

    public function withResourceBasePath(string $resourceBasePath): self
    {
        $newFactory = new self($resourceBasePath);
        foreach ($this->addedReaderTypes as $type => $readerFactory) {
            $newFactory->setReaderFactoryForType($type, $readerFactory);
        }
        return $newFactory;
    }

    private function createDecoratedReader(string $resource, array $options = [], string $typeOverride = null): ConfigReaderInterface
    {
        $readerOptions = array_diff_key($options, ['path' => true, 'exclude' => true]);
        $reader = $this->createReaderFromConfig($resource, $readerOptions, $typeOverride);

        if (!empty($options['path'])) {
            $reader = new NestedConfigReader($reader, $options['path']);
        }

        if (isset($options['exclude'])) {
            if (!is_array($options['exclude'])) {
                throw new InvalidArgumentException('Excluded array paths must be an array', 1510608229);
            }
            $reader = new ClosureConfigReader(
                function () use ($options, $reader) {
                    $config = $reader->readConfig();
                    foreach ($options['exclude'] as $overridePath) {
                        $config = Config::removeValue($config, $overridePath);
                    }
                    return $config;
                },
                function () use ($reader) {
                    return $reader->hasConfig();
                }
            );
        }

        return $reader;
    }

    private function createReaderFromConfig(string $resource, array $options = [], string $typeOverride = null): ConfigReaderInterface
    {
        // Expose parent resource path to third party factory closure within options
        $options['resourceBasePath'] = $this->resourceBasePath;
        $type = $options['type'] ?? pathinfo($resource, PATHINFO_EXTENSION);
        if ($typeOverride !== null) {
            $type = $typeOverride;
        }
        if (!isset($this->readerTypes[$type])) {
            throw new InvalidArgumentException(sprintf('Cannot create reader for resource "%s". Unkown type "%s"', $resource, $type), 1516837804);
        }
        if (is_string($this->readerTypes[$type])) {
            $options['type'] = $this->readerTypes[$type];
            return $this->createReaderFromConfig($resource, $options);
        }
        if ($this->readerTypes[$type] instanceof ConfigReaderInterface) {
            return $this->readerTypes[$type];
        }
        if (is_callable($this->readerTypes[$type])) {
            return call_user_func($this->readerTypes[$type], $resource, $options);
        }
        throw new InvalidArgumentException(sprintf('Invalid reader provided for type "%s". Must be callable or ConfigReaderInterface', $resource), 1516838223);
    }

    private function registerDefaultReaderTypes()
    {
        $this->readerTypes = [
            'env' => function ($resource) {
                return new EnvironmentReader($resource);
            },
            'glob' => function ($resource, array $options) {
                return new GlobFileReader($this->makeAbsolute($resource), $this->withResourceBasePath($options['resourceBasePath']));
            },
            'php' => function ($resource) {
                return new PhpFileReader($this->makeAbsolute($resource));
            },
            'rootReader' => function ($resource, array $options) {
                return new RootConfigFileReader($this->makeAbsolute($resource), $options, $this);
            },
            'yaml' => function ($resource) {
                return new YamlFileReader($this->makeAbsolute($resource));
            },
            'yml' => 'yaml',
        ];
    }

    private function makeAbsolute(string $path): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }
        if ($this->resourceBasePath === null) {
            throw new InvalidArgumentException(sprintf('Could not find resource "%s"', $path), 1516823055);
        }
        return $this->resourceBasePath . '/' . $path;
    }

    private function isAbsolutePath(string $path): bool
    {
        return $path[0] === '/' || $path[1] === ':';
    }
}
