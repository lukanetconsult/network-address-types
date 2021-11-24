<?php

declare(strict_types=1);

namespace LUKA\Network\IPv4;

use JsonSerializable;
use LUKA\Network\Address;
use LUKA\Network\Assert;
use LUKA\Network\CIDRAddress;

use function explode;
use function sprintf;

/**
 * @psalm-immutable
 */
final class CIDRv4Address extends CIDRAddress implements JsonSerializable
{
    private IPv4Address $address;

    public function __construct(IPv4Address $address, int $prefix)
    {
        Assert::range($prefix, 0, 32, 'Invalid ip v4 prefix length: %d');

        parent::__construct($prefix);
        $this->address = $address;
    }

    /**
     * @psalm-return self
     *
     * @psalm-pure
     */
    public static function fromString(string $address): self
    {
        Assert::contains($address, '/', 'Invalid cidr address format');

        [$ipAddress, $prefix] = explode('/', $address, 2);

        Assert::integerish($prefix, 'Invalid cidr address prefix "%s"');

        return new self(
            IPv4Address::fromString($ipAddress),
            (int)$prefix
        );
    }

    public function toAddress(): IPv4Address
    {
        return $this->address;
    }

    public function toNetwork(): IPv4Network
    {
        return new IPv4Network($this);
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

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
