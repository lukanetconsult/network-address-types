<?php

declare(strict_types=1);

namespace LUKA\Network;

use function strpos;

/**
 * @psalm-immutable
 */
abstract class CIDRAddress extends NetworkAddress
{
    protected int $prefixLength;

    protected function __construct(int $prefixLength)
    {
        $this->prefixLength = $prefixLength;
    }

    /**
     * @return IPv6\CIDRv6Address|IPv4\CIDRv4Address
     *
     * @psalm-pure
     */
    public static function fromString(string $address): self
    {
        return strpos($address, ':') !== false
            ? IPv6\CIDRv6Address::fromString($address)
            : IPv4\CIDRv4Address::fromString($address);
    }

    abstract public function toAddress(): IPAddress;

    abstract public function toNetwork(): Network;

    public function getPrefixLength(): int
    {
        return $this->prefixLength;
    }
}
