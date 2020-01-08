<?php

declare(strict_types=1);

namespace LUKA\Network\IPv6;

use GMP;
use JsonSerializable;
use LUKA\Network\Address;
use LUKA\Network\IPAddress;
use Webmozart\Assert\Assert;

use function bin2hex;
use function gmp_cmp;
use function gmp_export;
use function gmp_init;
use function inet_ntop;
use function inet_pton;
use function sprintf;
use function str_pad;
use function str_repeat;
use function strlen;

use const GMP_MSW_FIRST;
use const STR_PAD_LEFT;

/**
 * @psalm-immutable
 */
final class IPv6Address extends IPAddress implements JsonSerializable
{
    private GMP $address;

    public function __construct(GMP $address)
    {
        $this->address = $address;
    }

    /**
     * @psalm-pure
     * @psalm-return self
     */
    public static function fromString(string $address): self
    {
        // Special NULL-Address notation
        if ($address === '::') {
            return new self(gmp_init(str_repeat("00", 32), 16));
        }

        /** @psalm-suppress ImpureMethodCall Missing annotation in assert library */
        Assert::contains($address, ':', 'Invalid ip v6 address: "%s"');

        $bytes = inet_pton($address);

        /** @psalm-suppress ImpureMethodCall Missing annotation in assert library */
        Assert::string($bytes, sprintf('Invalid ip v6 address: "%s"', $address));

        return self::fromBinary($bytes);
    }

    /**
     * @psalm-pure
     */
    public static function fromBinary(string $bytes): self
    {
        /** @psalm-suppress ImpureMethodCall Missing annotation in assert library */
        Assert::lessThanEq(strlen($bytes), 16, 'Invalid IPv6 address length: %d bytes');
        return new self(gmp_init(bin2hex($bytes), 16));
    }

    public function toByteString(): string
    {
        return str_pad(
            gmp_export($this->address, 1, GMP_MSW_FIRST),
            16,
            "\x00",
            STR_PAD_LEFT
        );
    }

    public function toString(): string
    {
        return $this->isNull() ? '::' : inet_ntop($this->toByteString());
    }

    public function equals(Address $other): bool
    {
        return $other instanceof self
            && 0 === gmp_cmp($this->address, $other->address);
    }

    public function isNull(): bool
    {
        return gmp_cmp($this->address, '0') === 0;
    }

    public function toNumber(): GMP
    {
        return $this->address;
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
