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

use Helhum\ConfigLoader\ConfigurationExporter;
use Helhum\ConfigLoader\Processor\Placeholder\ConfigurationPlaceholder;
use Helhum\ConfigLoader\Processor\Placeholder\EnvironmentPlaceholder;
use Helhum\ConfigLoader\Processor\Placeholder\GlobalsPlaceholder;
use Helhum\ConfigLoader\Processor\Placeholder\PlaceholderCollection;

class ConfigurationExporterTest extends \PHPUnit_Framework_TestCase
{
    public function properlyExportsValueToPhpCodeDataProvider(): array
    {
        return [
            'Bool true' => [
                true,
                'true',
            ],
            'Bool false' => [
                false,
                'false',
            ],
            'Integer' => [
                42,
                '42',
            ],
            'Float' => [
                42.42,
                '42.42',
            ],
            'Null' => [
                null,
                'null',
            ],
            'Simple string' => [
                'foo',
                '\'foo\'',
            ],
            'String containing single quote' => [
                'fo\'o',
                '\'fo\\\'o\'',
            ],
            'String containing backslash' => [
                'fo\\o',
                '\'fo\\\\o\'',
            ],
            'Empty array' => [
                [],
                '[]',
            ],
            'Simple array' => [
                [42],
                '[
    42,
]',
            ],
            'Array with key' => [
                ['foo' => true],
                '[
    \'foo\' => true,
]',
            ],
        ];
    }

    /**
     * @param $value
     * @param string $phpCode
     * @param array $referenceConfig
     * @test
     * @dataProvider properlyExportsValueToPhpCodeDataProvider
     */
    public function properlyExportsValueToPhpCode($value, string $phpCode, array $referenceConfig = [])
    {
        $exporter = new ConfigurationExporter();
        $this->assertSame($phpCode, $exporter->exportPhpCode($value, $referenceConfig));
    }

    public function properlyExportsValueWithPlaceholdersToPhpCodeDataProvider(): array
    {
        return [
            'With env placeholder' => [
                '%env(foo)%',
                'getenv(\'foo\')',
            ],
            'With env placeholder with quote accessor' => [
                '%env(fo\'o)%',
                'getenv(\'fo\\\'o\')',
            ],
            'With global placeholder' => [
                '%global(foo.bar)%',
                '$GLOBALS[\'foo\'][\'bar\']',
            ],
        ];
    }

    /**
     * @param $value
     * @param string $phpCode
     * @param array $referenceConfig
     * @test
     * @dataProvider properlyExportsValueWithPlaceholdersToPhpCodeDataProvider
     */
    public function properlyExportsValueWithPlaceholdersToPhpCode($value, string $phpCode, array $referenceConfig = [])
    {
        $exporter = new ConfigurationExporter(
            new PlaceholderCollection([
                new EnvironmentPlaceholder(),
                new GlobalsPlaceholder(),
            ])
        );
        $this->assertSame($phpCode, $exporter->exportPhpCode($value, $referenceConfig));
    }

    /**
     * @test
     */
    public function confPlaceholderIsExportedStatic()
    {
        $value = [
            'foo' => '%env(FOO)%',
            'bar' => '%conf(foo)%',
        ];

        $expectedPhpCode = <<<'EOF'
[
    'foo' => getenv('FOO'),
    'bar' => getenv('FOO'),
]
EOF;

        $exporter = new ConfigurationExporter(
            new PlaceholderCollection([
                new EnvironmentPlaceholder(),
                new ConfigurationPlaceholder(),
            ])
        );
        $this->assertSame($expectedPhpCode, $exporter->exportPhpCode($value));
    }
}
