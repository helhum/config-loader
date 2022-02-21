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

class PlaceholderCollection implements \Iterator
{
    /**
     * @var PlaceholderInterface[]
     */
    private $placeholders;

    public function __construct(array $placeholders = [])
    {
        foreach ($placeholders as $placeholder) {
            if (!$placeholder instanceof PlaceholderInterface) {
                throw new \InvalidArgumentException('Placeholders must be instance of PlaceholderInterface', 1535898697);
            }
        }
        $this->placeholders = $placeholders;
    }

    public function supportedTypes(): array
    {
        $types = [];
        foreach ($this->placeholders as $placeHolder) {
            $types = array_merge($types, $placeHolder->supportedTypes());
        }

        return $types;
    }

    public function onlyStatic(): self
    {
        $nonPhpCapablePlaceholders = [];
        foreach ($this->placeholders as $placeHolder) {
            if (!$placeHolder instanceof PhpExportablePlaceholderInterface) {
                $nonPhpCapablePlaceholders[] = $placeHolder;
            }
        }

        return new self($nonPhpCapablePlaceholders);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->placeholders);
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        next($this->placeholders);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        key($this->placeholders);
    }

    public function valid(): bool
    {
        return current($this->placeholders) !== false;
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->placeholders);
    }
}
