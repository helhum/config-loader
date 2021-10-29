<?php
declare(strict_types=1);
namespace Helhum\ConfigLoader\Tests\Unit;

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
use Helhum\ConfigLoader\Reader\ClosureConfigReader;
use Helhum\ConfigLoader\Reader\ConfigReaderInterface;
use Helhum\ConfigLoader\Reader\RootConfigFileReader;
use PHPUnit\Framework\TestCase;

class ConfigurationReaderFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function createCreatesReader()
    {
        $factory = new ConfigurationReaderFactory();
        $this->assertInstanceOf(ConfigReaderInterface::class, $factory->createReader(__DIR__ . '/Fixture/conf/import.yml'));
    }

    /**
     * @test
     */
    public function createRootCreatesReader()
    {
        $factory = new ConfigurationReaderFactory();
        $this->assertInstanceOf(ConfigReaderInterface::class, $factory->createRootReader(__DIR__ . '/Fixture/conf/import.yml'));
    }

    /**
     * @test
     */
    public function createRootCreatesRootReader()
    {
        $factory = new ConfigurationReaderFactory();
        $this->assertInstanceOf(RootConfigFileReader::class, $factory->createRootReader(__DIR__ . '/Fixture/conf/import.yml'));
    }

    /**
     * @test
     */
    public function pathOptionWillBeRespected()
    {
        $factory = new ConfigurationReaderFactory();
        $factory->setReaderFactoryForType(
            'test',
            function () {
                return new ClosureConfigReader(
                    function () {
                        return ['foo' => 'bar'];
                    }
                );
            },
            false
        );

        $reader = $factory->createReader('foo', ['type' => 'test', 'path' => 'baz']);
        $this->assertInstanceOf(ConfigReaderInterface::class, $reader);
        $this->assertSame(['baz' => ['foo' => 'bar']], $reader->readConfig());
    }

    /**
     * @test
     */
    public function creatingRootReaderReturnsSameConfig()
    {
        $factory = new ConfigurationReaderFactory('/bla/');
        $factory->setReaderFactoryForType(
            'test',
            function () {
                return new ClosureConfigReader(
                    function () {
                        return ['foo' => 'bar'];
                    }
                );
            },
            false
        );

        $reader = $factory->createRootReader('foo', ['type' => 'test', 'path' => 'baz']);
        $this->assertInstanceOf(ConfigReaderInterface::class, $reader);
        $this->assertSame(['baz' => ['foo' => 'bar']], $reader->readConfig());
    }

    /**
     * @test
     */
    public function readerTypesCanBeRegistered()
    {
        $factory = new ConfigurationReaderFactory();
        $factory->setReaderFactoryForType(
            'test',
            function () {
                return new ClosureConfigReader(
                    function () {
                        return ['foo' => 'bar'];
                    }
                );
            },
            false
        );

        $reader = $factory->createReader('foo', ['type' => 'test']);
        $this->assertInstanceOf(ConfigReaderInterface::class, $reader);
        $this->assertSame(['foo' => 'bar'], $reader->readConfig());
    }

    /**
     * @test
     */
    public function readerTypeFactoryCanBeReader()
    {
        $factory = new ConfigurationReaderFactory();
        $factory->setReaderFactoryForType(
            'test',
            new ClosureConfigReader(
                function () {
                    return ['foo' => 'bar'];
                }
            ),
            false
        );

        $reader = $factory->createReader('foo', ['type' => 'test']);
        $this->assertInstanceOf(ConfigReaderInterface::class, $reader);
        $this->assertSame(['foo' => 'bar'], $reader->readConfig());
    }

    /**
     * @test
     */
    public function readerTypesCanReferenceOtherRegisteredTypes()
    {
        $factory = new ConfigurationReaderFactory();
        $factory->setReaderFactoryForType(
            'test',
            function ($resource, $options) {
                return new ClosureConfigReader(
                    function () use ($options) {
                        return ['foo' => 'bar'];
                    }
                );
            },
            false
        );
        $factory->setReaderFactoryForType('test2', 'test', false);

        $reader = $factory->createReader('foo', ['type' => 'test2']);
        $this->assertInstanceOf(ConfigReaderInterface::class, $reader);
        $this->assertSame(['foo' => 'bar'], $reader->readConfig());
    }

    /**
     * @test
     */
    public function createThrowsIfInvalidPathToResourceGiven()
    {
        $factory = new ConfigurationReaderFactory();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1516823055);
        $factory->createReader('import.yml');
    }

    /**
     * @test
     */
    public function createThrowsIfNoReaderFoundForResource()
    {
        $factory = new ConfigurationReaderFactory();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1516837804);
        $factory->createReader('import.test');
    }

    /**
     * @test
     */
    public function createThrowsIfReaderFactoryIsOfWrongTypeFoundForResource()
    {
        $factory = new ConfigurationReaderFactory();
        $factory->setReaderFactoryForType('test', ['not callable'], false);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1516838223);
        $factory->createReader('import.test');
    }
}
