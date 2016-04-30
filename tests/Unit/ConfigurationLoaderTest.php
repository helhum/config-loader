<?php
namespace Helhum\ConfigLoader\Tests;

/*
 * This file is part of the helhum TYPO3 configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Helhum\ConfigLoader\ConfigurationLoader;

/**
 * Class ConfigurationLoaderTest
 */
class ConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $baseConfig = array(
        'key' => 'base',
        'override_key' => 'base',
        'production_key' => 'base',
        'development_key' => 'base',
        'base_key' => 'base',
    );
    
    /**
     * @test
     */
    public function correctlyLoadsProductionContextConfiguration()
    {
        $configLoader = new ConfigurationLoader(
            $this->baseConfig,
            'Production',
            __DIR__ . '/Fixture/conf'
        );
        $configLoader->load();
        $this->assertSame('production', $this->baseConfig['key']);
    }


    /**
     * @test
     */
    public function correctlyLoadsSubContextConfiguration()
    {
        $configLoader = new ConfigurationLoader(
            $this->baseConfig,
            'Production/Foo',
            __DIR__ . '/Fixture/conf'
        );
        $configLoader->load();
        $this->assertSame('production.foo', $this->baseConfig['key']);
    }

    /**
     * @test
     */
    public function correctlyLoadsDevelopmentContextConfiguration()
    {
        $configLoader = new ConfigurationLoader(
            $this->baseConfig,
            'Development',
            __DIR__ . '/Fixture/conf'
        );
        $configLoader->load();
        $this->assertSame('development', $this->baseConfig['development_key']);
    }

    /**
     * @test
     */
    public function correctlyLoadsOverrideConfiguration()
    {
        $configLoader = new ConfigurationLoader(
            $this->baseConfig,
            'Production',
            __DIR__ . '/Fixture/conf'
        );
        $configLoader->load();
        $this->assertSame('override', $this->baseConfig['override_key']);
    }

    /**
     * @test
     */
    public function correctlyLoadsOverridesBaseConfiguration()
    {
        $configLoader = new ConfigurationLoader(
            $this->baseConfig,
            'Production',
            __DIR__ . '/Fixture/conf'
        );
        $configLoader->load();
        $this->assertSame('override', $this->baseConfig['override_key']);
    }

    /**
     * @test
     */
    public function correctlyLoadsEnvironmentConfiguration()
    {
        $_ENV['CONFIG_TEST__key'] = 'environment';
        $configLoader = new ConfigurationLoader(
            $this->baseConfig,
            'Production',
            __DIR__ . '/Fixture/conf',
            'CONFIG_TEST',
            '__'
        );
        $configLoader->load();
        $this->assertSame('environment', $this->baseConfig['key']);
    }
}