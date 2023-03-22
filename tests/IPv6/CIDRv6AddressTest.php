<?php

declare(strict_types=1);

namespace LUKATest\Network\IPv6;

use InvalidArgumentException;
use LUKA\Network\Address;
use LUKA\Network\IPv6\CIDRv6Address;
use LUKA\Network\IPv6\IPv6Address;
use LUKA\Network\MACAddress;
use PHPUnit\Framework\TestCase;

use function json_decode;
use function json_encode;

class CIDRv6AddressTest extends TestCase
{
    public function provideValidStringInput(): iterable
    {
        return [
            '2004:6fe8::/64' => ['2004:6fe8::/64', '2004:6fe8::/64'],
            '2004:6fe8::4/64' => ['2004:6fe8::4/64', '2004:6fe8::4/64'],
            '::1/8' => ['::1/8', '::1/8'],
        ];
    }

    /** @dataProvider provideValidStringInput */
    public function testShouldConstructFromString(string $input, string $expected): void
    {
        $subject = CIDRv6Address::fromString($input);
        self::assertSame($expected, $subject->toString());
    }

    public function provideInvalidStringInput(): iterable
    {
        return [
            'empty string' => [''],
            'missing length' => ['200f:65::'],
            'empty length' => ['200f:65::/'],
            'prefix to large' => ['200f:65::/129'],
            'non-numeric length' => ['200f:65::/f'],
            'empty address' => ['/64'],
        ];
    }

    /** @dataProvider provideInvalidStringInput */
    public function testShouldThrowInvalidArgumentOnnvalidStringInput(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        CIDRv6Address::fromString($input);
    }

    public function testShouldBeConvertableToIpAddress(): void
    {
        self::assertSame(
            '2004:6fe8::4',
            CIDRv6Address::fromString('2004:6fe8::4/64')
                ->toAddress()
                ->toString(),
        );
    }

    public function testShouldMatchEquality(): void
    {
        $subject = CIDRv6Address::fromString('2004:6fe8::4/64');
        $other   = CIDRv6Address::fromString('2004:6fe8::4/64');

        self::assertNotSame($subject, $other);
        self::assertTrue($subject->equals($other));
    }

    public function provideUnequalAddresses(): iterable
    {
        return [
            'different prefix' => ['2004:6fe8::4/64', CIDRv6Address::fromString('2004:6fe8::4/65')],
            'different address' => ['2004:6fe8::4/64', CIDRv6Address::fromString('2004:6fe8::5/64')],
            'different type (MAC)' => ['2004:6fe8::4/64', MACAddress::generateRandomAddress()],
            'different type (IP v6)' => ['::1/8', IPv6Address::fromString('::1')],
        ];
    }

    /** @dataProvider provideUnequalAddresses */
    public function testShouldNotMatchInequality(string $input, Address $other): void
    {
        $subject = CIDRv6Address::fromString($input);

        self::assertFalse($subject->equals($other));
    }

    public function testShouldBeConvertibleToNetwork(): void
    {
        $subject = CIDRv6Address::fromString('2004:6fe8::4/64');

        self::assertSame(
            '2004:6fe8::/64',
            $subject->toNetwork()->toString(),
        );
    }

    public function testShouldBeJsonSerializable(): void
    {
        $subject = CIDRv6Address::fromString('2004:6fe8::4/64');
        $json    = json_encode($subject);

        self::assertSame(
            $subject->toString(),
            CIDRv6Address::fromString(json_decode($json))->toString(),
        );
    }

    public function testShouldExposePrefixLength(): void
    {
        $subject = CIDRv6Address::fromString('2004:6fe8::/82');
        self::assertSame(82, $subject->getPrefixLength());
    }
}
