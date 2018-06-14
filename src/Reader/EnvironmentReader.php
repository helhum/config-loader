<?php
declare(strict_types=1);
namespace Helhum\ConfigLoader\Reader;

/*
 * This file is part of the helhum configuration loader package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Helhum\ConfigLoader\Config;

class EnvironmentReader implements ConfigReaderInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $keySeparator;

    public function __construct(string $prefix, string $keySeparator = '__')
    {
        $this->prefix = $prefix;
        $this->keySeparator = $keySeparator;
    }

    public function hasConfig(): bool
    {
        // Looping would be similarly expensive as reading
        return true;
    }

    public function readConfig(): array
    {
        $finalConfiguration = [];
        foreach ($_ENV as $name => $value) {
            if (!empty($this->prefix) && strpos($name, $this->prefix . $this->keySeparator) !== 0) {
                continue;
            }
            $finalConfiguration = Config::setValue(
                $finalConfiguration,
                str_replace($this->keySeparator, '.', substr($name, strlen($this->prefix . $this->keySeparator))),
                $value
            );
        }

        return $finalConfiguration;
    }
}
