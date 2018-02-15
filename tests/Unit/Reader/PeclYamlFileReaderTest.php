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

use Helhum\ConfigLoader\InvalidConfigurationFileException;
use Helhum\ConfigLoader\Reader\PeclYamlFileReader;

class PeclYamlFileReaderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('yaml')) {
            $this->markTestSkipped('Not able to test pecl yaml reader without pecl yaml extension being installed.');
        }
    }

    /**
     * @test
     */
    public function configIsNestedInGivenPath()
    {
        $reader = new PeclYamlFileReader(dirname(__DIR__) . '/Fixture/conf/broken_string.yml');
        $this->expectException(InvalidConfigurationFileException::class);
        $this->expectExceptionCode(1518629212);
        $reader->readConfig();
    }
}
