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

class ConstantPlaceholder implements PhpExportablePlaceholderInterface
{
    public function supportedTypes(): array
    {
        return ['const'];
    }

    public function supports(string $type): bool
    {
        return $type === 'const';
    }

    public function canReplace(string $accessor, array $referenceConfig = []): bool
    {
        return defined($accessor);
    }

    public function representsValue(string $accessor, array $referenceConfig = [])
    {
        return constant($accessor);
    }

    public function representsPhpCode(string $accessor, array $referenceConfig = []): string
    {
        return 'constant(\'' . addcslashes($accessor, '\\\'') . '\')';
    }
}
