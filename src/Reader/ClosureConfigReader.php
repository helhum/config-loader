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

/**
 * I can do anything reader.
 * Extremely useful in tests, but also when interacting with arbitrary frameworks.
 */
class ClosureConfigReader implements ConfigReaderInterface
{
    /**
     * @var \Closure
     */
    private $readConfigClosure;

    /**
     * @var \Closure
     */
    private $hasConfigClosure;

    public function __construct(\Closure $readConfigClosure, \Closure $hasConfigClosure = null)
    {
        $this->readConfigClosure = $readConfigClosure;
        $this->hasConfigClosure = $hasConfigClosure;
    }

    public function hasConfig(): bool
    {
        if ($this->hasConfigClosure) {
            return ($this->hasConfigClosure)();
        }

        return count($this->readConfig()) > 0;
    }

    public function readConfig(): array
    {
        return ($this->readConfigClosure)();
    }
}
