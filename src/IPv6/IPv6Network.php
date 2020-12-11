<?php

declare(strict_types=1);

namespace LUKA\Network\IPv6;

use GMP;
use LUKA\Network\Address;
use LUKA\Network\CIDRAddress;
use LUKA\Network\IPAddress;
use LUKA\Network\Network;

use function gmp_add;
use function gmp_and;
use function gmp_cmp;
use function gmp_init;
use function str_pad;
use function str_repeat;

use const STR_PAD_RIGHT;

/**
 * @psalm-immutable
 */
final class IPv6Network implements Network
{
    private CIDRv6Address $cidr;

    private GMP $netmask;

    public function __construct(CIDRv6Address $cidr)
    {
        $prefix        = $cidr->getPrefixLength();
        $this->netmask = gmp_init(str_pad(str_repeat('1', $prefix), 128, '0', STR_PAD_RIGHT), 2);
        $this->cidr    = new CIDRv6Address(
            new IPv6Address(
                gmp_and(
                    $cidr->toAddress()->toNumber(),
                    $this->netmask
                )
            ),
            $prefix
        );
    }

    public function toString(): string
    {
        return $this->cidr->toString();
    }

    public function equals(Address $other): bool
    {
        return $other instanceof self
            && $this->cidr->equals($other->cidr)
            && gmp_cmp($this->netmask, $other->netmask) === 0;
    }

    public function getRangeMinAddress(): CIDRv6Address
    {
        $prefix = $this->cidr->getPrefixLength();

        return $prefix === 128
            ? $this->cidr
            : new CIDRv6Address(
                new IPv6Address(
                    gmp_add(
                        $this->cidr->toAddress()->toNumber(),
                        gmp_init('1', 2)
                    )
                ),
                $prefix
            );
    }

    public function getRangeMaxAddress(): CIDRAddress
    {
        $prefix = $this->cidr->getPrefixLength();

        return $prefix === 128
            ? $this->cidr
            : new CIDRv6Address(
                new IPv6Address(
                    gmp_add(
                        $this->cidr->toAddress()->toNumber(),
                        gmp_init(str_repeat('1', 128 - $prefix), 2)
                    )
                ),
                $prefix
            );
    }

    public function containsAddress(IPAddress $address): bool
    {
        return $address instanceof IPv6Address
            && gmp_cmp(
                gmp_and(
                    $address->toNumber(),
                    $this->netmask
                ),
                $this->cidr->toAddress()->toNumber()
            ) === 0;
    }

    public function toCidrAddress(): CIDRAddress
    {
        return $this->cidr;
    }
}
