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

use Helhum\ConfigLoader\Reader\ConfigReaderInterface;
use Helhum\ConfigLoader\Reader\EnvironmentReader;
use Helhum\ConfigLoader\Reader\ExcludedConfigReader;
use Helhum\ConfigLoader\Reader\GlobFileReader;
use Helhum\ConfigLoader\Reader\NestedConfigReader;
use Helhum\ConfigLoader\Reader\PeclYamlFileReader;
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

    private function registerDefaultReaderTypes()
    {
        $this->readerTypes = [
            'env' => [
                'factory' => function (string $resource) {
                    return new EnvironmentReader($resource);
                },
                'isFileResource' => false,
            ],
            'glob' => [
                'factory' => function (string $resource) {
                    return new GlobFileReader($resource, $this);
                },
                'isFileResource' => true,
            ],
            'php' => [
                'factory' => function (string $resource) {
                    return new PhpFileReader($resource);
                },
                'isFileResource' => true,
            ],
            'rootReader' => [
                'factory' => function (string $resource, array $options) {
                    $factory = $this;
                    if ($this->isFileResource($resource, $options)) {
                        $factory = $this->withResourceBasePath(dirname($resource));
                    }

                    return new RootConfigFileReader($resource, $options, $factory);
                },
                'isFileResource' => false,
            ],
            'yaml' => [
                'factory' => function (string $resource) {
                    if (extension_loaded('yaml')) {
                        return new PeclYamlFileReader($resource);
                    }

                    return new YamlFileReader($resource);
                },
                'isFileResource' => true,
            ],
            'yml' => [
                'factory' => 'yaml',
            ],
        ];
    }

    /**
     * @param string $type
     * @param mixed $readerFactory
     * @param bool $isFileResource
     */
    public function setReaderFactoryForType(string $type, $readerFactory, bool $isFileResource)
    {
        $this->addedReaderTypes[$type]['factory'] = $readerFactory;
        $this->addedReaderTypes[$type]['isFileResource'] = $isFileResource;
        $this->readerTypes[$type]['factory'] = $readerFactory;
        $this->readerTypes[$type]['isFileResource'] = $isFileResource;
    }

    public function createReader(string $resource, array $options = []): ConfigReaderInterface
    {
        return $this->createDecoratedReader($resource, $options);
    }

    public function createRootReader(string $resource, array $options = []): ConfigReaderInterface
    {
        return $this->createDecoratedReader($resource, $options, 'rootReader');
    }

    public function isFileResource(string $resource, array $options)
    {
        return $this->readerTypes[$this->resolveType($resource, $options)]['isFileResource'];
    }

    private function withResourceBasePath(string $resourceBasePath): self
    {
        $newFactory = new self($resourceBasePath);
        foreach ($this->addedReaderTypes as $type => $readerFactoryOptions) {
            $newFactory->setReaderFactoryForType($type, $readerFactoryOptions['factory'], $readerFactoryOptions['isFileResource']);
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
            $reader = new ExcludedConfigReader($reader, ...$options['exclude']);
        }

        return $reader;
    }

    private function createReaderFromConfig(string $resource, array $options = [], string $typeOverride = null): ConfigReaderInterface
    {
        $type = $this->resolveType($resource, $options, $typeOverride);
        if ($this->readerTypes[$type]['factory'] instanceof ConfigReaderInterface) {
            return $this->readerTypes[$type]['factory'];
        }
        if (is_callable($this->readerTypes[$type]['factory'])) {
            return call_user_func($this->readerTypes[$type]['factory'], $this->makeAbsolute($resource, $options), $options);
        }
        throw new InvalidArgumentException(sprintf('Invalid reader provided for type "%s". Must be callable or ConfigReaderInterface', $resource), 1516838223);
    }

    private function resolveType(string $resource, array $options, string $typeOverride = null)
    {
        $type = $options['type'] ?? pathinfo($resource, PATHINFO_EXTENSION);
        if ($typeOverride !== null) {
            $type = $typeOverride;
        }
        if (!isset($this->readerTypes[$type])) {
            throw new InvalidArgumentException(sprintf('Cannot create reader for resource "%s". Unkown type "%s"', $resource, $type), 1516837804);
        }
        if (is_string($this->readerTypes[$type]['factory'])) {
            $options['type'] = $this->readerTypes[$type]['factory'];

            return $this->resolveType($resource, $options);
        }

        return $type;
    }

    private function makeAbsolute(string $resource, array $options): string
    {
        if (!$this->isFileResource($resource, $options) || $this->hasAbsolutePath($resource)) {
            return $resource;
        }
        if ($this->resourceBasePath === null) {
            throw new InvalidArgumentException(sprintf('Could not find resource "%s"', $resource), 1516823055);
        }

        return $this->resourceBasePath . '/' . $resource;
    }

    private function hasAbsolutePath(string $resource): bool
    {
        return $resource[0] === '/' || $resource[1] === ':';
    }
}
