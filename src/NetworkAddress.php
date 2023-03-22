<?php

declare(strict_types=1);

namespace LUKA\Network;

use function preg_match;
use function strpos;

/** @psalm-immutable */
abstract class NetworkAddress implements Address
{
    private const MAC_ADDRESS_FORMAT = '/^[0-9a-f]{2}([:-]?[0-9a-f]{2}){5}$/i';

    /**
     * @return MACAddress|IPv4\IPv4Address|IPv4\CIDRv4Address|IPv6\IPv6Address|IPv6\CIDRv6Address
     *
     * @psalm-pure
     */
    public static function fromString(string $address): NetworkAddress
    {
        if (preg_match(self::MAC_ADDRESS_FORMAT, $address)) {
            return MACAddress::fromString($address);
        }

        return strpos($address, '/') !== false
            ? CIDRAddress::fromString($address)
            : IPAddress::fromString($address);
    }
}
