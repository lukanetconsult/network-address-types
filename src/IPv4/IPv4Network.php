<?php

declare(strict_types=1);

namespace LUKA\Network\IPv4;

use LUKA\Network\Address;
use LUKA\Network\IPAddress;
use LUKA\Network\Network;

/** @psalm-immutable */
final class IPv4Network implements Network
{
    private CIDRv4Address $cidr;

    private int $netmask;

    public function __construct(CIDRv4Address $cidr)
    {
        $prefixLength  = $cidr->getPrefixLength();
        $this->netmask = -1 << 32 - $prefixLength & 0xffffffff;
        $this->cidr    = new CIDRv4Address(
            new IPv4Address(
                $cidr->toAddress()->toInt() & $this->netmask,
            ),
            $prefixLength,
        );
    }

    public function toString(): string
    {
        return $this->cidr->toString();
    }

    public function equals(Address $other): bool
    {
        return $other instanceof self
            && $this->cidr->equals($other->cidr);
    }

    public function getRangeMinAddress(): CIDRv4Address
    {
        $prefix = $this->cidr->getPrefixLength();

        return $prefix > 31
            ? $this->cidr
            : new CIDRv4Address(
                new IPv4Address($this->cidr->toAddress()->toInt() + 1),
                $prefix,
            );
    }

    public function getRangeMaxAddress(): CIDRv4Address
    {
        $prefix = $this->cidr->getPrefixLength();

        if ($prefix > 30) {
            return $prefix === 31 ? $this->getRangeMinAddress() : $this->cidr;
        }

        $max = 0xfffffffe & ~$this->netmask;

        return new CIDRv4Address(
            new IPv4Address($this->cidr->toAddress()->toInt() | $max),
            $prefix,
        );
    }

    public function containsAddress(IPAddress $address): bool
    {
        return $address instanceof IPv4Address
            && $this->cidr->toAddress()->toInt() === ($address->toInt() & $this->netmask);
    }

    public function toCidrAddress(): CIDRv4Address
    {
        return $this->cidr;
    }

    public function toBroadcastAddress(): CIDRv4Address
    {
        return new CIDRv4Address(
            new IPv4Address(
                $this->cidr->toAddress()->toInt() | (0xffffffff & ~$this->netmask),
            ),
            $this->cidr->getPrefixLength(),
        );
    }
}
