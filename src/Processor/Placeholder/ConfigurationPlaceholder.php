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
use Helhum\ConfigLoader\ConfigurationExporter;
use Helhum\ConfigLoader\PathDoesNotExistException;

class ConfigurationPlaceholder implements PlaceholderInterface
{
    public function supportedTypes(): array
    {
        return ['conf'];
    }

    public function supports(string $type): bool
    {
        return $type === 'conf';
    }

    public function canReplace(string $accessor, array $referenceConfig = []): bool
    {
        try {
            Config::getValue($referenceConfig, $accessor);
        } catch (PathDoesNotExistException $e) {
            return false;
        }

        return true;
    }

    public function representsValue(string $accessor, array $referenceConfig = [])
    {
        return Config::getValue($referenceConfig, $accessor);
    }
}
