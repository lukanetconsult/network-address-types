<?php

declare(strict_types=1);

namespace LUKATest\Network;

use InvalidArgumentException;
use LUKA\Network\IPAddress;
use LUKA\Network\IPv4\IPv4Address;
use LUKA\Network\IPv6\IPv6Address;
use PHPUnit\Framework\TestCase;

class IPAddressTest extends TestCase
{
    public function testShouldThrowOnUnknownAddressFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IPAddress::fromString('hello world');
    }

    public function provideFromStringTestData(): iterable
    {
        return [
            'ip v4' => ['127.0.0.1', IPv4Address::class],
            'ip v6' => ['fe80::a65:78:0:22', IPv6Address::class],
        ];
    }

    /** @dataProvider provideFromStringTestData */
    public function testShouldConstructFromString(
        string $address,
        string $expectedClass,
        ?string $expectedToString = null
    ): void {
        $result = IPAddress::fromString($address);

        self::assertInstanceOf($expectedClass, $result);
        self::assertSame($expectedToString ?? $address, $result->toString());
    }
}
