<?php
declare(strict_types=1);
namespace Helhum\ConfigLoader\Tests\Unit;

/*
 * This file is part of the helhum TYPO3 configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Helhum\ConfigLoader\ConfigurationLoader;
use Helhum\ConfigLoader\InvalidConfigurationFileException;
use Helhum\ConfigLoader\Reader\EnvironmentReader;
use Helhum\ConfigLoader\Reader\PhpFileReader;
use PHPUnit\Framework\TestCase;

class ConfigurationLoaderTest extends TestCase
{
    protected $baseConfig = [
        'key' => 'base',
        'override_key' => 'base',
        'production_key' => 'base',
        'development_key' => 'base',
        'base_key' => 'base',
    ];

    /**
     * @test
     */
    public function correctlyLoadsProductionContextConfiguration()
    {
        $context = 'production';
        $configLoader = new ConfigurationLoader(
            [
                new PhpFileReader(__DIR__ . '/Fixture/conf/default.php'),
                new PhpFileReader(__DIR__ . '/Fixture/conf/' . $context . '.php'),
            ]
        );
        $result = $configLoader->load();
        $this->assertSame('production', $result['key']);
    }

    /**
     * @test
     */
    public function throwsExceptionOnInvalidConfigFiles()
    {
        $this->expectException(InvalidConfigurationFileException::class);
        $configLoader = new ConfigurationLoader(
            [
                new PhpFileReader(__DIR__ . '/Fixture/conf/broken.php'),
            ]
        );
        $configLoader->load();
    }

    /**
     * @test
     */
    public function correctlyLoadsDevelopmentContextConfiguration()
    {
        $context = 'development';
        $configLoader = new ConfigurationLoader(
            [
                new PhpFileReader(__DIR__ . '/Fixture/conf/default.php'),
                new PhpFileReader(__DIR__ . '/Fixture/conf/' . $context . '.php'),
            ]
        );
        $result = $configLoader->load();
        $this->assertSame('development', $result['development_key']);
    }

    /**
     * @test
     */
    public function correctlyLoadsOverrideConfiguration()
    {
        $context = 'production';
        $configLoader = new ConfigurationLoader(
            [
                new PhpFileReader(__DIR__ . '/Fixture/conf/default.php'),
                new PhpFileReader(__DIR__ . '/Fixture/conf/' . $context . '.php'),
                new PhpFileReader(__DIR__ . '/Fixture/conf/override.php'),
            ]
        );
        $result = $configLoader->load();
        $this->assertSame('override', $result['override_key']);
    }

    /**
     * @test
     */
    public function correctlyLoadsEnvironmentConfiguration()
    {
        $_ENV['CONFIG_TEST__key'] = 'environment';
        $context = 'production';
        $configLoader = new ConfigurationLoader(
            [
                new PhpFileReader(__DIR__ . '/Fixture/conf/default.php'),
                new PhpFileReader(__DIR__ . '/Fixture/conf/' . $context . '.php'),
                new EnvironmentReader('CONFIG_TEST'),
            ]
        );
        $result = $configLoader->load();
        $this->assertSame('environment', $result['key']);
    }

    /**
     * @test
     */
    public function processorModifiesConfig()
    {
        $readerMock = $this->getMockBuilder('Helhum\\ConfigLoader\\Reader\\ConfigReaderInterface')->getMock();
        $readerMock->expects($this->once())->method('hasConfig')->willReturn(true);
        $readerMock->expects($this->once())
            ->method('readConfig')
            ->willReturn(
                [
                    'foo' => 'bar',
                ]
            );
        $processorMock = $this->getMockBuilder('Helhum\\ConfigLoader\\Processor\\ConfigProcessorInterface')->getMock();
        $processorMock->expects($this->once())
            ->method('processConfig')
            ->with(
                [
                    'foo' => 'bar',
                ]
            )
            ->willReturn(
                [
                    'foo' => 'baz',
                ]
            );

        $configLoader = new ConfigurationLoader(
            [
                $readerMock,
            ],
            [
                $processorMock,
            ]
        );
        $result = $configLoader->load();
        $this->assertSame('baz', $result['foo']);
    }
}
