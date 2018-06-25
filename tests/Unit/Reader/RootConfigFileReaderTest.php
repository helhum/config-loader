<?php
declare(strict_types=1);
namespace Helhum\ConfigLoader\Tests\Reader;

/*
 * This file is part of the helhum TYPO3 configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Helhum\ConfigLoader\ConfigurationReaderFactory;
use Helhum\ConfigLoader\InvalidArgumentException;
use Helhum\ConfigLoader\Reader\RootConfigFileReader;

class RootConfigFileReaderTest extends \PHPUnit_Framework_TestCase
{
    private $resourceBaseBath;

    public function __construct(...$arguments)
    {
        parent::__construct(...$arguments);
        $this->resourceBaseBath = dirname(__DIR__) . '/Fixture/conf';
    }

    /**
     * @test
     */
    public function canReadPhpFile()
    {
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/production.php', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $this->assertSame('production', $reader->readConfig()['key']);
    }

    /**
     * @test
     */
    public function canReadYamlFile()
    {
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/production.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $this->assertSame('production', $reader->readConfig()['key']);
    }

    /**
     * @test
     */
    public function canReadEnvironment()
    {
        $reader = new RootConfigFileReader('FOO', ['type' => 'env'], new ConfigurationReaderFactory($this->resourceBaseBath));
        $_ENV['FOO__key'] = 'production';
        try {
            $this->assertSame('production', $reader->readConfig()['key']);
        } finally {
            unset($_ENV['FOO__key']);
        }
    }

    /**
     * @test
     */
    public function importedConfigIsOverriddenByMainConfig()
    {
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/import.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $result = $reader->readConfig();
        $this->assertSame('import', $result['key']);
        $this->assertSame('import', $result['override_key']);
    }

    /**
     * @test
     */
    public function importRecursionCausesException()
    {
        $this->expectException(InvalidArgumentException::class);
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/recursion1.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $reader->readConfig();
    }

    /**
     * @test
     */
    public function nestedImportRecursionCausesException()
    {
        $this->expectException(InvalidArgumentException::class);
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/recursion3.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $reader->readConfig();
    }

    /**
     * @test
     */
    public function brokenImportCausesException()
    {
        $this->expectException(InvalidArgumentException::class);
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/broken_import1.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $reader->readConfig();
    }

    /**
     * @test
     */
    public function brokenImportResourceCausesException()
    {
        $this->expectException(InvalidArgumentException::class);
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/broken_import1.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $reader->readConfig();
    }

    /**
     * @test
     */
    public function notAvailableImportResourceCausesExceptionByDefault()
    {
        $this->expectException(InvalidArgumentException::class);
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/broken_import1.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $reader->readConfig();
    }

    /**
     * @test
     */
    public function notAvailableImportResourceIsIgnoredWhenConfigured()
    {
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/graceful_import.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $this->assertSame([], $reader->readConfig());
    }

    /**
     * @test
     */
    public function notAvailableImportResourceIsIgnoredWhenOptional()
    {
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/optional_import.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $this->assertSame([], $reader->readConfig());
    }

    /**
     * @test
     */
    public function importGlobImportsAllFiles()
    {
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/glob.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $this->assertSame('bar', $reader->readConfig()['foo']);
        $this->assertSame('foobar', $reader->readConfig()['baz']);
    }

    /**
     * @test
     */
    public function canImportNestedStructure()
    {
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/nested.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $_ENV['FOO__key'] = 'production';
        try {
            $config = $reader->readConfig();
            $this->assertSame('bar', $config['nested']['foo']);
            $this->assertSame('foobar', $config['nested']['baz']);
        } finally {
            unset($_ENV['FOO__key']);
        }
    }

    /**
     * @test
     */
    public function canImportComplexStructures()
    {
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/complex/root.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath . '/complex'));
        $_ENV['FOO__key'] = 'production';
        try {
            $config = $reader->readConfig();
            $this->assertSame('first_one', $config['key_first_one']);
            $this->assertSame('second_two', $config['key_second_two']);
            $this->assertSame('production', $config['key']);
        } finally {
            unset($_ENV['FOO__key']);
        }
    }

    /**
     * @test
     */
    public function canOverridePathsFromImportedConfig()
    {
        $reader = new RootConfigFileReader($this->resourceBaseBath . '/import.yml', [], new ConfigurationReaderFactory($this->resourceBaseBath));
        $this->assertSame(
            ['bar' => 'baz'],
            $reader->readConfig()['nested']['foo']
        );
    }
}
