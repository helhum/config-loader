# Configuration Loader [![Build Status](https://travis-ci.org/helhum/config-loader.svg?branch=master)](https://travis-ci.org/helhum/config-loader)

This is just a class, which helps you to merge a base configuration with configuration
from different contexts and the environment.

Just require it using composer: `composer require helhum/config-loader`

## Usage:

```php
$context = 'production';
$confDir = '/path/to/conf';
$configLoader = new \Helhum\ConfigLoader\ConfigurationLoader(
    array(
        new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/default.php'),
        new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/' . $context . '.php'),
        new \Helhum\ConfigLoader\Reader\EnvironmentReader('PREFIX'),
        new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/override.php'),
    )
);
$config = $configLoader->load();
```

## Usage cached:

```php
$context = 'production';
$confDir = '/path/to/conf';
$cacheDir = '/path/to/cache';
$cacheIdentifier = md5($context . filemtime('/path/to/.env');
$configLoader = new \Helhum\ConfigLoader\CachedConfigurationLoader(
    $cacheDir,
    $cacheIdentifier,
    function() use ($confDir, $context) {
        return new \Helhum\ConfigLoader\ConfigurationLoader(
            array(
                new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/default.php'),
                new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/' . $context . '.php'),
                new \Helhum\ConfigLoader\Reader\EnvironmentReader('PREFIX'),
                new \Helhum\ConfigLoader\Reader\PhpFileReader($confDir . '/override.php'),
            )
        );
    }
);
$config = $configLoader->load();
```

## Feedback

Any feedback is appreciated. Please write bug reports, feature request, create pull requests, or just drop me a "thank you" via [Twitter](https://twitter.com/helhum) or spread the word.

Thank you!
