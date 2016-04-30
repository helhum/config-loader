# Configuration Loader [![Build Status](https://travis-ci.org/helhum/config-loader.svg?branch=master)](https://travis-ci.org/helhum/config-loader)

This is just a class, which helps you to merge a base configuration with configuration
from different contexts and the environment.

Just require it using composer: `composer require helhum/config-loader`

Usage:

```php
$configLoader = new ConfigurationLoader(
    $myBaseConfigVarAsReference,
    'Production',
    __DIR__ . '/Fixture/conf',
    'CONFIG_TEST',
    '__'
);
$configLoader->load();
```

## Feedback

Any feedback is appreciated. Please write bug reports, feature request, create pull requests, or just drop me a "thank you" via [Twitter](https://twitter.com/helhum) or spread the word.

Thank you!
