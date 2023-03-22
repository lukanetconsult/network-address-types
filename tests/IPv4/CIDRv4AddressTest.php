<?php

declare(strict_types=1);

namespace LUKATest\Network\IPv4;

use InvalidArgumentException;
use LUKA\Network\Address;
use LUKA\Network\IPv4\CIDRv4Address;
use LUKA\Network\IPv4\IPv4Address;
use LUKA\Network\IPv4\IPv4Network;
use LUKA\Network\IPv6\IPv6Address;
use LUKA\Network\MACAddress;
use PHPUnit\Framework\TestCase;

class CIDRv4AddressTest extends TestCase
{
    public function provideValidValues(): iterable
    {
        $list = [
            '0.0.0.0/0',
            '255.255.255.255/24',
            '127.0.0.1/8',
            '10.0.5.153/16',
            '86.134.56.253/32',
        ];

        foreach ($list as $ip) {
            yield $ip => [$ip];
        }
    }

    public function provideInvalidValues(): iterable
    {
        $list = [
            'a.7.s.s',
            'localhost',
            '127.0.0/66',
            '10.0.5.256/24',
            '10.0.256.2',
            '86.134.56.253/32.5',
            '86.134.56.253/32/5',
            '',
            '10.0.5.153/128',
        ];

        foreach ($list as $ip) {
            yield 'value: ' . $ip => [$ip];
        }
    }

    /** @dataProvider provideValidValues */
    public function testShouldAcceptValidAddresses(string $fixture): void
    {
        $subject = CIDRv4Address::fromString($fixture);
        self::assertSame($fixture, $subject->toString());
    }

    /** @dataProvider provideInvalidValues */
    public function testShouldThrowOnInvalidAddresses(string $fixture): void
    {
        $this->expectException(InvalidArgumentException::class);
        CIDRv4Address::fromString($fixture);
    }

    public function testShouldBeConvertibleToIpAddress(): void
    {
        self::assertTrue(
            CIDRv4Address::fromString('128.6.119.56/24')
                ->toAddress()
                ->equals(IPv4Address::fromString('128.6.119.56')),
        );
    }

    public function testShouldBeConvertibleToNetwork(): void
    {
        $subject = CIDRv4Address::fromString('128.6.119.56/24');
        self::assertTrue($subject->toNetwork()->equals(new IPv4Network($subject)));
    }

    public function testShouldMatchEquality(): void
    {
        $subject = CIDRv4Address::fromString('128.6.119.56/24');
        $other   = CIDRv4Address::fromString('128.6.119.56/24');

        self::assertNotSame($other, $subject);
        self::assertTrue($subject->equals($other));
    }

    public function provideUnequalAddresses(): iterable
    {
        return [
            'different prefix' => ['128.6.119.56/24', CIDRv4Address::fromString('128.6.119.56/25')],
            'different address' => ['128.6.119.56/24', CIDRv4Address::fromString('128.6.119.57/24')],
            'different type (MAC)' => ['128.6.119.56/24', MACAddress::generateRandomAddress()],
            'different type (IP v6)' => ['127.0.0.1/8', IPv6Address::fromString('::1')],
        ];
    }

    /** @dataProvider provideUnequalAddresses */
    public function testShouldNotMatchInequality(string $address, Address $other): void
    {
        $subject = CIDRv4Address::fromString($address);

        self::assertNotSame($other, $subject);
        self::assertFalse($subject->equals($other));
    }

    public function testShouldExposePrefixLength(): void
    {
        $subject = CIDRv4Address::fromString('10.0.0.0/12');

        self::assertSame(12, $subject->getPrefixLength());
    }
}
