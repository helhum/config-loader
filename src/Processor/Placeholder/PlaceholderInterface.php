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

interface PlaceholderInterface
{
    public function supportedTypes(): array;

    public function supports(string $type): bool;

    public function canReplace(string $accessor, array $referenceConfig = []): bool;

    public function representsValue(string $accessor, array $referenceConfig = []);

    public function representsPhpCode(string $accessor, array $referenceConfig = []): string;
}
