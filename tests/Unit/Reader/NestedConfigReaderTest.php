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

use Helhum\ConfigLoader\Reader\NestedConfigReader;

class NestedConfigReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function configIsNestedInGivenPath()
    {
        $readerMock = $this->getMockBuilder('Helhum\\ConfigLoader\\Reader\\ConfigReaderInterface')->getMock();
        $readerMock->expects($this->once())
            ->method('readConfig')
            ->willReturn(
                [
                    'foo' => 'bar',
                ]
            );
        $reader = new NestedConfigReader($readerMock, 'bla.fasel');
        $this->assertSame(
            [
                'bla' => [
                    'fasel' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
            $reader->readConfig()
        );
    }
}
