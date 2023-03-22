<?php

declare(strict_types=1);

namespace LUKA\Network;

use InvalidArgumentException;

use function gettype;
use function is_int;
use function is_numeric;
use function is_object;
use function is_string;
use function preg_match;
use function sprintf;
use function strpos;

/** @internal */
class Assert
{
    /**
     * @psalm-pure
     * @psalm-assert string $value
     */
    public static function string(mixed $value, string|null $message = null): void
    {
        if (is_string($value)) {
            return;
        }

        self::throwInvalidArgument($message ?? 'Expected %s to be a string.', self::typeStringFor($value));
    }

    /**
     * @psalm-pure
     * @psalm-assert int $value
     */
    public static function integer(mixed $value, string|null $message = null): void
    {
        if (is_int($value)) {
            return;
        }

        self::throwInvalidArgument($message ?? 'Expected %s to be an integer.', self::typeStringFor($value));
    }

    /** @psalm-pure */
    public static function range(int $value, int $min, int $max, string|null $message = null): void
    {
        if ($value >= $min && $value <= $max) {
            return;
        }

        self::throwInvalidArgument($message ?? 'Expected %d to be in range of %d - %d.', $value, $min, $max);
    }

    /** @psalm-pure */
    public static function contains(string $value, string $substring, string|null $message = null): void
    {
        if (strpos($value, $substring) !== false) {
            return;
        }

        self::throwInvalidArgument($message ?? 'Expected "%s" to contain "%s".', $value, $substring);
    }

    /**
     * @psalm-pure
     * @psalm-assert numeric $value
     */
    public static function integerish(string|int $value, string|null $message = null): void
    {
        // phpcs:disable SlevomatCodingStandard.Operators.DisallowEqualOperators
        if (is_numeric($value) && $value == (int)$value) {
            return;
        }

        // phpcs:enable
        self::throwInvalidArgument($message ?? 'Expected "%s" to be an integer-like value', $value);
    }

    /** @psalm-pure */
    public static function lessThanEq(int $value, int $max, string|null $message = null): void
    {
        if ($value <= $max) {
            return;
        }

        self::throwInvalidArgument($message ?? 'Expected %d to be less than %d', $value, $max);
    }

    /** @psalm-pure */
    public static function regex(string $value, string $regex, string|null $message = null): void
    {
        if (preg_match($regex, $value)) {
            return;
        }

        self::throwInvalidArgument($message ?? 'Expected "%s" to match "%s".', $value, $regex);
    }

    /** @psalm-pure */
    private static function throwInvalidArgument(string $message, float|int|string ...$args): void
    {
        throw new InvalidArgumentException(
            sprintf(
                $message,
                ...$args,
            ),
        );
    }

    /** @psalm-pure */
    private static function typeStringFor(mixed $value): string
    {
        return is_object($value) ? $value::class : gettype($value);
    }
}
