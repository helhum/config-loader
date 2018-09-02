<?php
declare(strict_types=1);
namespace Helhum\ConfigLoader\Processor\Placeholder;

/*
 * This file is part of the helhum configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Helhum\ConfigLoader\Config;
use Helhum\ConfigLoader\PathDoesNotExistException;

class GlobalsPlaceholder implements PhpExportablePlaceholderInterface
{
    public function supportedTypes(): array
    {
        return ['global'];
    }

    public function supports(string $type): bool
    {
        return $type === 'global';
    }

    public function canReplace(string $accessor, array $referenceConfig = []): bool
    {
        try {
            Config::getValue($GLOBALS, $accessor);
        } catch (PathDoesNotExistException $e) {
            return false;
        }

        return true;
    }

    public function representsValue(string $accessor, array $referenceConfig = [])
    {
        return Config::getValue($GLOBALS, $accessor);
    }

    public function representsPhpCode(string $accessor, array $referenceConfig = []): string
    {
        $globalPath = str_getcsv($accessor, '.');

        return '$GLOBALS[\'' . implode('\'][\'', array_map([$this, 'escapePhpValue'], $globalPath)) . '\']';
    }

    private function escapePhpValue(string $value): string
    {
        return addcslashes($value, '\\\'');
    }
}
