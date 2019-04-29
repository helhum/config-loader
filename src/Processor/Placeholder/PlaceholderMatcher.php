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
    private static $PLACEHOLDER_PATTERN = '/%([a-z]+)\((int:|bool:|string:|float:)?([^)]+)\)%/';

    /**
     * @var string[]
     */
    private $supportedTypes;

    public function __construct(array $supportedTypes = null)
    {
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * @deprecated
     * @param $value
     * @param array|null $types
     * @return bool
     */
    public function isPlaceHolder($value, array $types = null): bool
    {
        trigger_error(__FUNCTION__ . ' is deprecated. Use PlaceholderMatcher::hasPlaceHolders instead', \E_USER_DEPRECATED);

        return $this->hasPlaceHolders($value, $types);
    }

    public function hasPlaceHolders($value, array $types = null): bool
    {
        if (!$this->matches($value)) {
            return false;
        }
        $types = $types ?: $this->supportedTypes;
        if ($types === null) {
            return true;
        }
        $placeHolders = $this->extractPlaceHolders($value, null);
        foreach ($placeHolders as $placeHolder) {
            if (in_array($placeHolder->getType(), $types, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @deprecated
     * @param $value
     * @param array|null $types
     * @return PlaceholderMatch
     */
    public function extractPlaceHolder($value, array $types = null): PlaceholderMatch
    {
        trigger_error(__FUNCTION__ . ' is deprecated. Use PlaceholderMatcher::extractPlaceHolders instead', \E_USER_DEPRECATED);

        return $this->extractPlaceHolders($value, $types)[0];
    }

    /**
     * @param $value
     * @param array|null $types
     * @return PlaceholderMatch[]
     */
    public function extractPlaceHolders($value, array $types = null): array
    {
        if (func_num_args() < 2) {
            $types = $types ?: $this->supportedTypes;
        }

        if (!$this->matches($value)) {
            throw new \UnexpectedValueException('Cannot extract placeholder as value does not contain a placeholder', 1556492134);
        }
        preg_match_all(self::$PLACEHOLDER_PATTERN, $value, $matches);
        $placeHolderCount = count($matches[0]);
        $placeHolderMatches = [];
        for ($index = 0; $index < $placeHolderCount; $index++) {
            $placeHolder = new PlaceholderMatch(
                $matches[0][$index],
                $matches[1][$index],
                $matches[2][$index] ? rtrim($matches[2][$index], ':') : '',
                $matches[3][$index],
                $matches[0][$index] === $value
            );
            if ($types !== null && $placeHolder->isDirectMatch() && !in_array($matches[1][$index], $types, true)) {
                throw new \UnexpectedValueException('Cannot extract placeholder because it isn\'t in given types', 1556492137);
            }
            $placeHolderMatches[] = $placeHolder;
        }

        return $placeHolderMatches;
    }

    private function matches($value): bool
    {
        return is_string($value) && preg_match(self::$PLACEHOLDER_PATTERN, $value);
    }
}
