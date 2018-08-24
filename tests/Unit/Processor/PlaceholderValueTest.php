<?php
declare(strict_types=1);
namespace Helhum\ConfigLoader\Tests\Unit\Processor;

/*
 * This file is part of the helhum TYPO3 configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Helhum\ConfigLoader\Config;
use Helhum\ConfigLoader\InvalidConfigurationFileException;
use Helhum\ConfigLoader\Processor\PlaceholderValue;

class PlaceholderValueTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $GLOBALS['foo'] = 'bar';
        $GLOBALS['bar'] = '%env(bar)%';
        $GLOBALS['integer'] = 42;
        putenv('foo=bar');
        putenv('bar=%env(foo)%');
        putenv('recursion=%env(recursion)%');
    }

    protected function tearDown()
    {
        unset($GLOBALS['foo'], $GLOBALS['bar'], $GLOBALS['integer']);
        putenv('foo');
        putenv('bar');
        putenv('recursion');
    }

    public function placeholderDataProvider(): array
    {
        return [
            'Replaces environment' => [
                '%env(foo)%',
                'bar',
            ],
            'Recursively replaces environment' => [
                '%env(bar)%',
                'bar',
            ],
            'Replaces environment inline' => [
                'is: %env(foo)%',
                'is: bar',
            ],
            'Removes unmatched inline' => [
                'is: %env(baz)%',
                'is: ',
            ],
            'Replaces constant' => [
                '%const(PHP_BINARY)%',
                PHP_BINARY,
            ],
            'Replaces global var' => [
                '%global(foo)%',
                'bar',
            ],
            'Recursively replaces global var' => [
                '%global(bar)%',
                'bar',
            ],
            'Replaces global var and keeps type' => [
                '%global(integer)%',
                42,
            ],
            'Replaces conf value' => [
                '%conf(foo.bar)%',
                42,
                [
                    'foo' => [
                        'bar' => 42,
                    ],
                ],
            ],
            'Replaces conf value with dots' => [
                '%conf("foo.bar".baz)%',
                42,
                [
                    'foo.bar' => [
                        'baz' => 42,
                    ],
                ],
            ],
            'Recursively replaces conf value' => [
                '%conf(foo.bar)%',
                'bar',
                [
                    'foo' => [
                        'bar' => '%env(foo)%',
                    ],
                ],
            ],
            'does not replace if wrong syntax' => [
                '%env()%',
                '%env()%',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider placeholderDataProvider
     * @param string $placeHolder
     * @param mixed $expectedValue
     * @param array $config
     */
    public function correctlyReplacesPlaceholders(string $placeHolder, $expectedValue, array $config = [])
    {
        $config['placeholder'] = $placeHolder;
        $subject = new PlaceholderValue(false);
        $result = $subject->processConfig($config);
        $this->assertSame($expectedValue, $result['placeholder']);
    }

    public function invalidConfigThrowsExceptionDataProvider(): array
    {
        return [
            'Recursion in conf throws exception' => [
                1519593176,
                [
                    'foo' => [
                        'bar' => '%conf(foo)%',
                    ],
                ],
            ],
            'Recursion in env throws exception' => [
                1519593176,
                [
                    'foo' => [
                        'bar' => '%env(recursion)%',
                    ],
                ],
            ],
            'Not existing env var throws exception' => [
                1519640359,
                [
                    'foo' => [
                        'bar' => '%env(bla)%',
                    ],
                ],
            ],
            'Not defined constant throws exception' => [
                1519640359,
                [
                    'foo' => [
                        'bar' => '%const(bla)%',
                    ],
                ],
            ],
            'Not existing config path throws exception' => [
                1519640359,
                [
                    'foo' => [
                        'bar' => '%conf(bla)%',
                    ],
                ],
            ],
            'Not existing config path in global throws exception' => [
                1519640359,
                [
                    'foo' => [
                        'bar' => '%global(bla)%',
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidConfigThrowsExceptionDataProvider
     * @param int|null $expectedExceptionCode
     * @param array $config
     */
    public function invalidConfigThrowsExceptionInStrictMode(int $expectedExceptionCode, array $config)
    {
        $subject = new PlaceholderValue();
        $this->expectException(InvalidConfigurationFileException::class);
        $this->expectExceptionCode($expectedExceptionCode);
        $subject->processConfig($config);
    }

    public function invalidConfigReplacesPlaceholderInNonStrictModeWithNullDataProvider(): array
    {
        return [
            'Not existing env var' => [
                [
                    'foo' => [
                        'bar' => '%env(bla)%',
                    ],
                ],
            ],
            'Not defined constant' => [
                [
                    'foo' => [
                        'bar' => '%const(bla)%',
                    ],
                ],
            ],
            'Not existing config path' => [
                [
                    'foo' => [
                        'bar' => '%conf(bla)%',
                    ],
                ],
            ],
            'Not existing config path in global' => [
                [
                    'foo' => [
                        'bar' => '%global(bla)%',
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidConfigReplacesPlaceholderInNonStrictModeWithNullDataProvider
     * @param array $config
     */
    public function invalidConfigReplacesPlaceholderInNonStrictModeWithNull(array $config)
    {
        $subject = new PlaceholderValue(false);
        $processedConfig = $subject->processConfig($config);
        $expectedConfig = [
            'foo' => [
                'bar' => null,
            ],
        ];
        $this->assertSame($expectedConfig, $processedConfig);
    }
}
