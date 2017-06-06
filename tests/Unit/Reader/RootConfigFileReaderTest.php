<?php
namespace Helhum\ConfigLoader\Tests\Reader;

/*
 * This file is part of the helhum TYPO3 configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Helhum\ConfigLoader\InvalidArgumentException;
use Helhum\ConfigLoader\Reader\RootConfigFileReader;

class RootConfigFileReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canReadPhpFile()
    {
        $reader = new RootConfigFileReader(dirname(__DIR__) . '/Fixture/conf/production.php');
        $this->assertSame('production', $reader->readConfig()['key']);
    }

    /**
     * @test
     */
    public function canReadYamlFile()
    {
        $reader = new RootConfigFileReader(dirname(__DIR__) . '/Fixture/conf/production.yml');
        $this->assertSame('production', $reader->readConfig()['key']);
    }

    /**
     * @test
     */
    public function canReadEnvironment()
    {
        $reader = new RootConfigFileReader('FOO', 'env');
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
    public function importConfigFilesOverridesConfig()
    {
        $reader = new RootConfigFileReader(dirname(__DIR__) . '/Fixture/conf/import.yml');
        $result = $reader->readConfig();
        $this->assertSame('production', $result['key']);
        $this->assertSame('override', $result['override_key']);
    }

    /**
     * @test
     */
    public function importRecursionCausesException()
    {
        $this->expectException(InvalidArgumentException::class);
        $reader = new RootConfigFileReader(dirname(__DIR__) . '/Fixture/conf/recursion1.yml');
        $reader->readConfig();
    }

    /**
     * @test
     */
    public function nestedImportRecursionCausesException()
    {
        $this->expectException(InvalidArgumentException::class);
        $reader = new RootConfigFileReader(dirname(__DIR__) . '/Fixture/conf/recursion3.yml');
        $reader->readConfig();
    }

    /**
     * @test
     */
    public function brokenImportCausesException()
    {
        $this->expectException(InvalidArgumentException::class);
        $reader = new RootConfigFileReader(dirname(__DIR__) . '/Fixture/conf/broken_import1.yml');
        $reader->readConfig();
    }

    /**
     * @test
     */
    public function brokenImportResourceCausesException()
    {
        $this->expectException(InvalidArgumentException::class);
        $reader = new RootConfigFileReader(dirname(__DIR__) . '/Fixture/conf/broken_import1.yml');
        $reader->readConfig();
    }

    /**
     * @test
     */
    public function notAvailableImportResourceCausesExceptionByDefault()
    {
        $this->expectException(InvalidArgumentException::class);
        $reader = new RootConfigFileReader(dirname(__DIR__) . '/Fixture/conf/broken_import1.yml');
        $reader->readConfig();
    }

    /**
     * @test
     */
    public function notAvailableImportResourceIsIgnoredWhenConfigured()
    {
        $reader = new RootConfigFileReader(dirname(__DIR__) . '/Fixture/conf/graceful_import.yml');
        $this->assertSame([], $reader->readConfig());
    }

    /**
     * @test
     */
    public function importGlobImportsAllFiles()
    {
        $reader = new RootConfigFileReader(dirname(__DIR__) . '/Fixture/conf/glob.yml');
        $this->assertSame('bar', $reader->readConfig()['foo']);
        $this->assertSame('foobar', $reader->readConfig()['baz']);
    }

    /**
     * @test
     */
    public function canImportNestedStructure()
    {
        $reader = new RootConfigFileReader(dirname(__DIR__) . '/Fixture/conf/nested.yml');
        $this->assertSame('bar', $reader->readConfig()['nested']['foo']);
        $this->assertSame('foobar', $reader->readConfig()['nested']['baz']);
    }
}
