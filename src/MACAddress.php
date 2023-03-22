<?php

declare(strict_types=1);

namespace LUKA\Network;

use GMP;
use JsonSerializable;

use function assert;
use function bin2hex;
use function gmp_add;
use function gmp_cmp;
use function gmp_export;
use function gmp_init;
use function gmp_or;
use function gmp_strval;
use function gmp_testbit;
use function implode;
use function random_bytes;
use function sprintf;
use function str_pad;
use function str_repeat;
use function strlen;
use function strtr;
use function substr;

use const GMP_MSW_FIRST;
use const STR_PAD_LEFT;

/** @psalm-immutable */
final class MACAddress extends NetworkAddress implements JsonSerializable
{
    public const MAC_ADDRESS_FORMAT = '/^[0-9a-f]{2}([:-]?[0-9a-f]{2}){5}$/i';

    private string $vendorId;

    public function __construct(private GMP $address)
    {
        $binary = gmp_export($address, 1, GMP_MSW_FIRST);

        Assert::lessThanEq(
            strlen($binary),
            6,
            sprintf('Invalid mac address value "%s" (overflow).', gmp_strval($address, 16)),
        );

        $this->vendorId = bin2hex(substr($binary, 0, 3));
    }

    /**
     * @psalm-return self
     *
     * @psalm-pure
     */
    public static function fromString(string $address): self
    {
        Assert::regex($address, self::MAC_ADDRESS_FORMAT, 'Invalid mac address: "%s"');

        return new self(gmp_init(strtr($address, [':' => '', '-' => '']), 16));
    }

    /**
     * Generate a random mac address with a prefix
     *
     * The prefix is omitted or empty, this method will ensure that the generated
     * Address is  flagged as a local one.
     *
     * @param string $prefix Prefix as hex number, 3 bytes at maximum.
     */
    public static function generateRandomAddress(string $prefix = ''): self
    {
        if ($prefix === '') {
            return new MACAddress(
                gmp_or(
                    gmp_init(bin2hex(random_bytes(6)), 16),
                    gmp_init('020000000000', 16),
                ),
            );
        }

        Assert::regex($prefix, '/^([a-f0-9]{2}){0,3}$/i', 'Invalid mac address prefix: "%s"');

        $prefixLength = strlen($prefix);
        $randomLength = 6 - (int)($prefixLength / 2);

        assert($randomLength > 0);

        return new MACAddress(
            gmp_add(
                gmp_init($prefix . str_repeat('0', 12 - $prefixLength), 16),
                gmp_init(bin2hex(random_bytes($randomLength)), 16),
            ),
        );
    }

    public function toString(): string
    {
        $hex      = str_pad(gmp_strval($this->address, 16), 12, '0', STR_PAD_LEFT);
        $segments = [];

        for ($offset = 0; $offset < 12; $offset += 2) {
            $segments[] = substr($hex, $offset, 2);
        }

        return implode(':', $segments);
    }

    public function toByteString(): string
    {
        return gmp_export($this->address, 1, GMP_MSW_FIRST);
    }

    public function equals(Address $other): bool
    {
        return $other instanceof self
            && gmp_cmp($this->address, $other->address) === 0;
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function getVendorID(): string
    {
        return $this->vendorId;
    }

    public function isLocal(): bool
    {
        return gmp_testbit($this->address, 41);
    }

    public function isBroadCast(): bool
    {
        return gmp_cmp($this->address, gmp_init('ffffffffffff', 16)) === 0;
    }
}
