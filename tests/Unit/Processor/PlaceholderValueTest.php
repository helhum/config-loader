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

use Helhum\ConfigLoader\InvalidConfigurationFileException;
use Helhum\ConfigLoader\Processor\PlaceholderValue;

class PlaceholderValueTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $GLOBALS['foo'] = 'bar';
        $GLOBALS['integer'] = 42;
        putenv('foo=bar');
    }

    protected function tearDown()
    {
        unset($GLOBALS['foo'], $GLOBALS['integer']);
        putenv('foo');
    }

    public function placeholderDataProvider()
    {
        return [
            'Replaces environment' => [
                '%env(foo)%',
                'bar',
            ],
            'Replaces environment inline' => [
                'is: %env(foo)%',
                'is: bar',
            ],
            'Replaces constant' => [
                '%const(PHP_BINARY)%',
                PHP_BINARY,
            ],
            'Replaces global var' => [
                '%global(foo)%',
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
        $subject = new PlaceholderValue();
        $result = $subject->processConfig($config);
        $this->assertSame($expectedValue, $result['placeholder']);
    }

    public function invalidConfigThrowsExceptionDataProvider()
    {
        return [
            'Recursion throws exception' => [
                1519593176,
                [
                    'foo' => [
                        'bar' => '%conf(foo)%',
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
                1519640600,
                [
                    'foo' => [
                        'bar' => '%const(bla)%',
                    ],
                ],
            ],
            'Not existing config path throws exception' => [
                1519640588,
                [
                    'foo' => [
                        'bar' => '%conf(bla)%',
                    ],
                ],
            ],
            'Not existing config path in global throws exception' => [
                1519640631,
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


    public function invalidConfigDoesNotReplacePlaceholderInNonStrictModeDataProvider()
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
     * @dataProvider invalidConfigDoesNotReplacePlaceholderInNonStrictModeDataProvider
     * @param array $config
     */
    public function invalidConfigDoesNotReplacePlaceholderInNonStrictMode(array $config)
    {
        $subject = new PlaceholderValue(false);
        $processedConfig = $subject->processConfig($config);
        $this->assertSame($config, $processedConfig);
    }
}
