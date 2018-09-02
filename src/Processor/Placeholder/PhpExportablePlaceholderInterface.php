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

interface PhpExportablePlaceholderInterface extends PlaceholderInterface
{
    public function representsPhpCode(string $accessor, array $referenceConfig = []): string;
}
