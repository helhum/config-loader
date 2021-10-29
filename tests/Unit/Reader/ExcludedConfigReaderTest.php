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

use Helhum\ConfigLoader\Reader\ConfigReaderInterface;
use Helhum\ConfigLoader\Reader\ExcludedConfigReader;
use Helhum\ConfigLoader\Tests\Unit\Fixture\ArrayReader;
use PHPUnit\Framework\TestCase;

class ExcludedConfigReaderTest extends TestCase
{
    public function givenPathsAreExcludedDataProvider(): \Generator
    {
        yield 'given path is exclude' => [
            new ArrayReader(['foo' => 'bar', 'bla' => 'burp']),
            ['foo'],
            ['bla' => 'burp'],
        ];

        yield 'not existing path is ignored' => [
            new ArrayReader(['foo' => 'bar', 'bla' => 'burp']),
            ['foot'],
            ['foo' => 'bar', 'bla' => 'burp'],
        ];

        yield 'no exclusion returns original' => [
            new ArrayReader(['foo' => 'bar', 'bla' => 'burp']),
            [],
            ['foo' => 'bar', 'bla' => 'burp'],
        ];
    }

    /**
     * @param ConfigReaderInterface $reader
     * @param array $excludedPaths
     * @param array $expectedConfig
     * @test
     * @dataProvider givenPathsAreExcludedDataProvider
     */
    public function givenPathsAreExcluded(ConfigReaderInterface $reader, array $excludedPaths, array $expectedConfig): void
    {
        self::assertSame($expectedConfig, (new ExcludedConfigReader($reader, ...$excludedPaths))->readConfig());
    }
}
