<?php

declare(strict_types=1);

namespace LUKA\Network\IPv4;

use JsonSerializable;
use LUKA\Network\Address;
use LUKA\Network\IPAddress;
use Webmozart\Assert\Assert;

use function ip2long;
use function is_int;
use function long2ip;
use function pack;
use function sprintf;
use function unpack;

/**
 * @psalm-immutable
 */
final class IPv4Address extends IPAddress implements JsonSerializable
{
    private int $address;

    public function __construct(int $address)
    {
        /** @psalm-suppress ImpureMethodCall Missing annotation in assert library */
        Assert::lessThanEq($address, 0xffffffff, 'Invalid IP-Address value "0x%x" (overflow).');
        $this->address = $address;
    }

    /**
     * @psalm-pure
     * @psalm-return self
     */
    public static function fromString(string $address): self
    {
        $value = ip2long($address);

        /** @psalm-suppress ImpureMethodCall Missing annotation in assert library */
        Assert::integer($value);
        return new self($value);
    }

    /**
     * @psalm-pure
     */
    public static function fromByteString(string $bytes): self
    {
        /** @var int $address */
        $address = unpack('Naddr', $bytes)['addr'];
        return new self($address);
    }

    public function toString(): string
    {
        return long2ip($this->address);
    }

    public function equals(Address $other): bool
    {
        return $other instanceof self
            && $this->address === $other->address;
    }

    public function isNull(): bool
    {
        return $this->address === 0;
    }

    public function toInt(): int
    {
        return $this->address;
    }

    public function toByteString(): string
    {
        return pack('N', $this->address);
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
