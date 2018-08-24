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

class PlaceholderMatch
{
    /**
     * @var string
     */
    private $placeholder;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $accessor;

    /**
     * @var bool
     */
    private $directMatch;

    /**
     * @var bool|null
     */
    private $key;

    /**
     * @var string|null
     */
    private $path;

    public function __construct(
        string $placeholder,
        string $type,
        string $accessor,
        bool $directMatch,
        bool $key = null,
        string $path = null
    ) {
        $this->placeholder = $placeholder;
        $this->type = $type;
        $this->accessor = $accessor;
        $this->directMatch = $directMatch;
        $this->key = $key;
        $this->path = $path;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAccessor(): string
    {
        return $this->accessor;
    }

    public function isDirectMatch(): bool
    {
        return $this->directMatch;
    }

    public function isKey(): bool
    {
        return $this->key ?? false;
    }

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }
}
