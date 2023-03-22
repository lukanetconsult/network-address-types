<?php

declare(strict_types=1);

namespace LUKATest\Network;

use InvalidArgumentException;
use LUKA\Network\IPv4\CIDRv4Address;
use LUKA\Network\IPv4\IPv4Address;
use LUKA\Network\IPv6\CIDRv6Address;
use LUKA\Network\IPv6\IPv6Address;
use LUKA\Network\MACAddress;
use LUKA\Network\NetworkAddress;
use PHPUnit\Framework\TestCase;

class NetworkAddressTest extends TestCase
{
    public function testShouldThrowOnUnknownAddressFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        NetworkAddress::fromString('hello world');
    }

    public function provideFromStringTestData(): iterable
    {
        return [
            'ip v4' => ['127.0.0.1', IPv4Address::class],
            'ip v6' => ['fe80::a65:78:0:22', IPv6Address::class],
            'cidr v4' => ['192.168.0.0/24', CIDRv4Address::class],
            'cidr v6' => ['fe80::/64', CIDRv6Address::class],
            'mac (colon)' => ['28:e5:65:78:00:22', MACAddress::class],
            'mac (dash)' => ['28-e5-65-78-00-22', MACAddress::class, '28:e5:65:78:00:22'],
            'mac (no delim)' => ['28e565780022', MACAddress::class, '28:e5:65:78:00:22'],
        ];
    }

    /** @dataProvider provideFromStringTestData */
    public function testShouldConstructFromString(
        string $address,
        string $expectedClass,
        string|null $expectedToString = null,
    ): void {
        $result = NetworkAddress::fromString($address);

        self::assertInstanceOf($expectedClass, $result);
        self::assertSame($expectedToString ?? $address, $result->toString());
    }
}
