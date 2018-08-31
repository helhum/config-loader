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

class PlaceholderMatcher
{
    // Should rather be private const, once we raise minimum PHP version to 7.1
    private static $PLACEHOLDER_PATTERN = '/%([a-z]+)\(([^)]+)\)%/';

    /**
     * @var string[]
     */
    private $supportedTypes;

    public function __construct(array $supportedTypes = null)
    {
        $this->supportedTypes = $supportedTypes;
    }

    public function isPlaceHolder($value, array $types = null): bool
    {
        $types = $types ?: $this->supportedTypes;

        return $this->matches($value)
            && ($types === null || in_array($this->extractPlaceHolder($value, null)->getType(), $types, true));
    }

    public function extractPlaceHolder($value, array $types = null): PlaceholderMatch
    {
        if (func_num_args() < 2) {
            $types = $types ?: $this->supportedTypes;
        }

        if (!$this->matches($value)) {
            throw new \UnexpectedValueException('Cannot extract placeholder as value does not contain a placeholder', 1534932991);
        }
        preg_match(self::$PLACEHOLDER_PATTERN, $value, $matches);
        if ($types !== null && !in_array($matches[1], $types, true)) {
            throw new \UnexpectedValueException('Cannot extract placeholder because it isn\'t in given types', 1534933036);
        }

        return new PlaceholderMatch(
            $matches[0],
            $matches[1],
            $matches[2],
            $matches[0] === $value
        );
    }

    private function matches($value): bool
    {
        return is_string($value) && preg_match(self::$PLACEHOLDER_PATTERN, $value);
    }
}
