<?php

declare(strict_types=1);

namespace LUKA\Network\IPv6;

use JsonSerializable;
use LUKA\Network\Address;
use LUKA\Network\Assert;
use LUKA\Network\CIDRAddress;

use function explode;
use function sprintf;

/**
 * @psalm-immutable
 */
final class CIDRv6Address extends CIDRAddress implements JsonSerializable
{
    private IPv6Address $address;

    public function __construct(IPv6Address $address, int $prefixLength)
    {
        Assert::range($prefixLength, 0, 128, 'Invalid ip v6 prefix: %d');

        parent::__construct($prefixLength);
        $this->address = $address;
    }

    /**
     * @psalm-pure
     * @psalm-return self
     */
    public static function fromString(string $address): self
    {
        Assert::contains($address, '/', 'Invalid cidr address format');

        [$ipAddress, $prefix] = explode('/', $address, 2);

        Assert::integerish($prefix, 'Invalid cidr address prefix "%s"');

        return new self(
            IPv6Address::fromString($ipAddress),
            (int)$prefix
        );
    }

    public function toString(): string
    {
        return sprintf('%s/%d', $this->address->toString(), $this->prefixLength);
    }

    public function equals(Address $other): bool
    {
        return $other instanceof self
            && $this->address->equals($other->address)
            && $this->prefixLength === $other->prefixLength;
    }

    public function toAddress(): IPv6Address
    {
        return $this->address;
    }

    public function toNetwork(): IPv6Network
    {
        return new IPv6Network($this);
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
