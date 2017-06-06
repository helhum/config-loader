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
                        'bar' => 42
                    ]
                ]
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
     */
    public function correctlyReplacesPlaceholders(string $placeHolder, $expectedValue, $config = [])
    {
        $config['placeholder'] = $placeHolder;
        $subject = new PlaceholderValue();
        $result = $subject->processConfig($config);
        $this->assertSame($expectedValue, $result['placeholder']);
    }
}
