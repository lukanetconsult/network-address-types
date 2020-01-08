<?php

declare(strict_types=1);

namespace LUKA\Network;

use function strpos;

/**
 * @psalm-immutable
 */
abstract class IPAddress extends NetworkAddress
{
    /**
     * @psalm-pure
     * @return IPv4\IPv4Address|IPv6\IPv6Address
     */
    public static function fromString(string $address): self
    {
        if (strpos($address, ':') !== false) {
            return IPv6\IPv6Address::fromString($address);
        }

        return IPv4\IPv4Address::fromString($address);
    }

    abstract public function toByteString(): string;
}
