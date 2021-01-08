<?php

declare(strict_types=1);

namespace LUKATest\Network\IPv6;

use LUKA\Network\Address;
use LUKA\Network\IPAddress;
use LUKA\Network\IPv4\CIDRv4Address;
use LUKA\Network\IPv4\IPv4Address;
use LUKA\Network\IPv4\IPv4Network;
use LUKA\Network\IPv6\CIDRv6Address;
use LUKA\Network\IPv6\IPv6Address;
use LUKA\Network\IPv6\IPv6Network;
use PHPUnit\Framework\TestCase;

class IPv6NetworkTest extends TestCase
{
    public function provideNormalizePrefixTestData(): iterable
    {
        return [
            '2004:6fe8::/64' => ['2004:6fe8::/64', '2004:6fe8::/64'],
            '2004:6fe8::4/64' => ['2004:6fe8::4/64', '2004:6fe8::/64'],
            'ff02::1:2/8' => ['ff02::1:2/8', 'ff00::/8'],
            '2004:6fe8::4/0' => ['2004:6fe8::4/0', '::/0'],
            '2004:6fe8::4/128' => ['2004:6fe8::4/128', '2004:6fe8::4/128'],
        ];
    }

    /** @dataProvider provideNormalizePrefixTestData */
    public function testShouldNormalizeCidrPrefix(string $input, string $expected): void
    {
        $subject = new IPv6Network(CIDRv6Address::fromString($input));
        self::assertSame($expected, $subject->toString());
        self::assertSame($expected, $subject->toCidrAddress()->toString());
    }

    public function provideRangeTestData(): iterable
    {
        $data = [
            '200e:cafe:5::/120' => ['200e:cafe:5::1/120', '200e:cafe:5::ff/120'],
            '200e:cafe:5::15/120' => ['200e:cafe:5::1/120', '200e:cafe:5::ff/120'],
            '200e:cafe:5::1/128' => ['200e:cafe:5::1/128', '200e:cafe:5::1/128'],
            '200e:cafe:5::1/127' => ['200e:cafe:5::1/127', '200e:cafe:5::1/127'],
            '200e:cafe:5::1/126' => ['200e:cafe:5::1/126', '200e:cafe:5::3/126'],
        ];

        foreach ($data as $input => $range) {
            yield $input => [$input, ...$range];
        }
    }

    /** @dataProvider provideRangeTestData */
    public function testShouldProvideNetworkRange(string $input, string $min, string $max): void
    {
        $subject = new IPv6Network(CIDRv6Address::fromString($input));

        self::assertSame($min, $subject->getRangeMinAddress()->toString());
        self::assertSame($max, $subject->getRangeMaxAddress()->toString());
    }

    public function provideContainsAddressTestData(): iterable
    {
        return [
            '200e:cafe:5::/120' => ['200e:cafe:5::/120', '200e:cafe:5::15'],
            '200e:cafe:5::/64' => ['200e:cafe:5::/64', '200e:cafe:5::56a4:7f54'],
        ];
    }

    /** @dataProvider provideContainsAddressTestData */
    public function testShouldContainMatchingAddress(string $network, string $address): void
    {
        $subject = new IPv6Network(CIDRv6Address::fromString($network));

        self::assertTrue($subject->containsAddress(IPv6Address::fromString($address)));
    }

    public function provideNotContainsAddressTestData(): iterable
    {
        return [
            'not in prefix' => ['200e:cafe:5::/120', IPv6Address::fromString('200e:cafe:5::115')],
            'different address type' => ['200e:cafe:5::/64', IPv4Address::fromString('129.0.0.1')],
        ];
    }

    /** @dataProvider provideNotContainsAddressTestData */
    public function testShouldNotContainNonMatchingAddress(string $network, IPAddress $address): void
    {
        $subject = new IPv6Network(CIDRv6Address::fromString($network));

        self::assertFalse($subject->containsAddress($address));
    }

    public function testShouldCompareEquality(): void
    {
        $first  = new IPv6Network(CIDRv6Address::fromString('::1/10'));
        $second = new IPv6Network(CIDRv6Address::fromString('::1/10'));

        self::assertNotSame($first, $second);
        self::assertTrue($first->equals($second));
    }

    public function provideInequalityTestData(): iterable
    {
        return [
            'different network' => ['::1/10', new IPv6Network(CIDRv6Address::fromString('::1/8'))],
            'different type' => ['::1/10', new IPv4Network(CIDRv4Address::fromString('127.0.0.1/8'))],
            'cidr' => ['::1/10', CIDRv6Address::fromString('::1/10')],
        ];
    }

    /** @dataProvider provideInequalityTestData */
    public function testShouldNotMatchInequality(string $subject, Address $other): void
    {
        self::assertFalse((new IPv6Network(CIDRv6Address::fromString($subject)))->equals($other));
    }
}
