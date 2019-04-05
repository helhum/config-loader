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

use Helhum\ConfigLoader\ConfigurationReaderFactory;

class GlobFileReader implements ConfigReaderInterface
{
    /**
     * @var string
     */
    private $resourceFile;

    /**
     * @var ConfigurationReaderFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $globFiles;

    public function __construct(string $resourceFile, ConfigurationReaderFactory $factory = null)
    {
        $this->resourceFile = $resourceFile;
        $this->factory = $factory ?: new ConfigurationReaderFactory();
    }

    public function hasConfig(): bool
    {
        if ($this->globFiles === null) {
            $this->globFiles = glob($this->resourceFile, (\defined('GLOB_BRACE') ? GLOB_BRACE : 0));
        }

        return !empty($this->globFiles);
    }

    public function readConfig(): array
    {
        if (!$this->hasConfig()) {
            return [];
        }

        $readers = [];

        foreach ($this->globFiles as $settingsFile) {
            $readers[] = $this->factory->createRootReader($settingsFile);
        }

        return (new CollectionReader(...$readers))->readConfig();
    }
}
