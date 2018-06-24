# Configuration Loader [![Build Status](https://travis-ci.org/helhum/config-loader.svg?branch=master)](https://travis-ci.org/helhum/config-loader)

This is just a class, which helps you to merge a base configuration with configuration
from different contexts and the environment.

Just require it using composer: `composer require helhum/config-loader`

## Basic usage:

```php
$context = 'production';
$confDir = '/path/to/conf';
$configReaderFactory = new \Helhum\ConfigLoader\ConfigurationReaderFactory($confDir);
$configLoader = new \Helhum\ConfigLoader\ConfigurationLoader(
    [
        $configReaderFactory->createReader($confDir . '/default.php'),
        $configReaderFactory->createReader($confDir . '/' . $context . '.php'),
        $configReaderFactory->createReader('PREFIX', ['type' => 'env']),
        $configReaderFactory->createReader($confDir . '/override.php'),
    ]
);
$config = $configLoader->load();
```

## Basic usage cached:

```php
$context = 'production';
$confDir = '/path/to/conf';
$cacheDir = '/path/to/cache';
$cacheIdentifier = md5($context . filemtime('/path/to/.env'));
$configReaderFactory = new \Helhum\ConfigLoader\ConfigurationReaderFactory($confDir);
$configLoader = new \Helhum\ConfigLoader\CachedConfigurationLoader(
    $cacheDir,
    $cacheIdentifier,
    function() use ($confDir, $context, $configReaderFactory) {
        return new \Helhum\ConfigLoader\ConfigurationLoader(
            [
                $configReaderFactory->createReader($confDir . '/default.php'),
                $configReaderFactory->createReader($confDir . '/' . $context . '.php'),
                $configReaderFactory->createReader('PREFIX', ['type' => 'env']),
                $configReaderFactory->createReader($confDir . '/override.php'),
            ]
        );
    }
);
$config = $configLoader->load();
```

## Using processors
It is possible to add one or more processors to the config loader.

```php
$context = 'production';
$confDir = '/path/to/conf';
$configReaderFactory = new \Helhum\ConfigLoader\ConfigurationReaderFactory($confDir);
$configLoader = new \Helhum\ConfigLoader\ConfigurationLoader(
    [
        $configReaderFactory->createReader($confDir . '/config.php'),
    ],
    [
        new \Helhum\ConfigLoader\Processor\PlaceholderValue(),
    ]
);
$config = $configLoader->load();
```

## Advanced usage
Instead of hard coding which configuration sources should be included, it is possible to include
multiple sources from within one configuration file.

```php
$context = 'production';
$confDir = '/path/to/conf';
$configReaderFactory = new \Helhum\ConfigLoader\ConfigurationReaderFactory($confDir);
$configLoader = new \Helhum\ConfigLoader\ConfigurationLoader(
    [
        $configReaderFactory->createRootReader($confDir . '/config.yaml'),
    ]
);
$config = $configLoader->load();
```

The configuration file can then include an `import` section:

```yaml
imports:
    - { resource: 'config.*.yml', type: glob }
    - { resource: 'env.yml' }
```

## Feedback

Any feedback is appreciated. Please write bug reports, feature request, create pull requests, or just drop me a "thank you" via [Twitter](https://twitter.com/helhum) or spread the word.

Thank you!
