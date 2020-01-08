<?php

declare(strict_types=1);

namespace LUKATest\Network;

use InvalidArgumentException;
use LUKA\Network\CIDRAddress;
use LUKA\Network\IPv4\CIDRv4Address;
use LUKA\Network\IPv6\CIDRv6Address;
use PHPUnit\Framework\TestCase;

class CIDRAddressTest extends TestCase
{
    public function testShouldThrowOnUnknownAddressFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CIDRAddress::fromString('hello world');
    }

    public function provideFromStringTestData(): iterable
    {
        return [
            'cidr v4' => ['192.168.0.0/24', CIDRv4Address::class],
            'cidr v6' => ['fe80::/64', CIDRv6Address::class],
        ];
    }

    /** @dataProvider provideFromStringTestData */
    public function testShouldConstructFromString(
        string $address,
        string $expectedClass,
        string $expectedToString = null
    ): void
    {
        $result = CIDRAddress::fromString($address);

        self::assertInstanceOf($expectedClass, $result);
        self::assertSame($expectedToString ?? $address, $result->toString());
    }

}
