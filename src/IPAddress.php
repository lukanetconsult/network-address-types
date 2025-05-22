<?php

declare(strict_types=1);

namespace LUKA\Network;

use Override;

use function str_contains;

/** @psalm-immutable */
abstract class IPAddress extends NetworkAddress
{
    /**
     * @return IPv4\IPv4Address|IPv6\IPv6Address
     *
     * @psalm-pure
     */
    #[Override]
    public static function fromString(string $address): self
    {
        if (str_contains($address, ':')) {
            return IPv6\IPv6Address::fromString($address);
        }

        return IPv4\IPv4Address::fromString($address);
    }

    abstract public function toByteString(): string;
}
