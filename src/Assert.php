<?php

declare(strict_types=1);

namespace LUKA\Network;

use InvalidArgumentException;

use function get_class;
use function gettype;
use function is_int;
use function is_numeric;
use function is_object;
use function is_string;
use function preg_match;
use function sprintf;
use function strpos;

/**
 * @internal
 */
class Assert
{
    /**
     * @psalm-pure
     * @psalm-assert string $value
     * @param mixed $value
     */
    public static function string($value, string $message = null): void
    {
        if (!is_string($value)) {
            self::throwInvalidArgument($message ?? 'Expected %s to be a string.', self::typeStringFor($value));
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert int $value
     * @param mixed $value
     */
    public static function integer($value, string $message = null): void
    {
        if (!is_int($value)) {
            self::throwInvalidArgument($message ?? 'Expected %s to be an integer.', self::typeStringFor($value));
        }
    }

    /**
     * @psalm-pure
     */
    public static function range(int $value, int $min, int $max, string $message = null): void
    {
        if ($value < $min || $value > $max) {
            self::throwInvalidArgument($message ?? 'Expected %d to be in range of %d - %d.', $value, $min, $max);
        }
    }

    /**
     * @psalm-pure
     */
    public static function contains(string $value, string $substring, string $message = null): void
    {
        if (strpos($value, $substring) === false) {
            self::throwInvalidArgument($message ?? 'Expected "%s" to contain "%s".', $value, $substring);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert numeric $value
     * @param string|int $value
     */
    public static function integerish($value, string $message = null): void
    {
        if (!is_numeric($value) || $value != (int)$value) {
            self::throwInvalidArgument($message ?? 'Expected "%s" to be an integer-like value', $value);
        }
    }

    /**
     * @psalm-pure
     */
    public static function lessThanEq(int $value, int $max, string $message = null): void
    {
        if ($value > $max) {
            self::throwInvalidArgument($message ?? 'Expected %d to be less than %d', $value, $max);
        }
    }

    /**
     * @psalm-pure
     */
    public static function regex(string $value, string $regex, string $message = null): void
    {
        if (!preg_match($regex, $value)) {
            self::throwInvalidArgument($message ?? 'Expected "%s" to match "%s".', $value, $regex);
        }
    }

    /**
     * @psalm-pure
     * @param float|int|string ...$args
     */
    private static function throwInvalidArgument(string $message, ...$args): void
    {
        throw new InvalidArgumentException(
            sprintf(
                $message,
                ...$args
            )
        );
    }

    /**
     * @psalm-pure
     * @param mixed $value
     */
    private static function typeStringFor($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
