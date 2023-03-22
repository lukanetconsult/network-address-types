<?php

declare(strict_types=1);

namespace LUKATest\Network\IPv6;

use InvalidArgumentException;
use LUKA\Network\Address;
use LUKA\Network\IPv4\IPv4Address;
use LUKA\Network\IPv6\CIDRv6Address;
use LUKA\Network\IPv6\IPv6Address;
use PHPUnit\Framework\TestCase;

use function json_decode;
use function json_encode;
use function str_pad;

use const STR_PAD_LEFT;

class IPv6AddressTest extends TestCase
{
    public function provideValidAddressStrings(): iterable
    {
        return [
            '::1' => ['::1', '::1'],
            '::' => ['::', '::'],
            'a6f4:56::' => ['a6f4:56::', 'a6f4:56::'],
            '0:0:0:0:0:0:0:1' => ['0:0:0:0:0:0:0:1', '::1'],
            'fe80::a65:78:0:22' => ['fe80::a65:78:0:22', 'fe80::a65:78:0:22'],
        ];
    }

    /** @dataProvider provideValidAddressStrings */
    public function testShouldConstructFromString(string $input, string $expected): void
    {
        $subject = IPv6Address::fromString($input);
        self::assertSame($expected, $subject->toString());
    }

    public function provideInvalidAddressStrings(): iterable
    {
        return [
            'multiple zero omits' => ['fe80::7::22'],
            'too many segments' => ['fe80:0:0:0:0:0:0:0:1'],
            'non-hex digits' => ['fe80::z6:1'],
            'segment too large' => ['fe80::f0893:1'],
        ];
    }

    /** @dataProvider  provideInvalidAddressStrings */
    public function testShouldThrowInvalidArgumentOnInvalidAddressStringInput(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        IPv6Address::fromString($input);
    }

    public function testShouldExportCorrectByteString(): void
    {
        self::assertSame(
            str_pad("\x01", 16, "\x00", STR_PAD_LEFT),
            IPv6Address::fromString('::1')->toByteString(),
        );
    }

    public function testShouldRestoreFromBinary(): void
    {
        self::assertSame(
            '::1',
            IPv6Address::fromBinary("\x01")->toString(),
        );
    }

    public function testShouldMatchEquality(): void
    {
        $subject = IPv6Address::fromString('fe80::a65:78:0:22');
        $other   = IPv6Address::fromString('fe80::a65:78:0:22');

        self::assertNotSame($subject, $other);
        self::assertTrue($subject->equals($other));
    }

    public function provideInequalityTestData(): iterable
    {
        return [
            'different address' => ['fe80::a65:78:0:22', IPv6Address::fromString('fe80::a65:78:0:21')],
            'different type' => ['::1', IPv4Address::fromString('127.0.0.1')],
            'cidr' => ['::1', CIDRv6Address::fromString('::1/64')],
        ];
    }

    /** @dataProvider provideInequalityTestData */
    public function testShouldNotMatchInequality(string $subject, Address $other): void
    {
        self::assertFalse(IPv6Address::fromString($subject)->equals($other));
    }

    public function testShouldExportNumericValue(): void
    {
        $subject = IPv6Address::fromString('fe80::a65:78:0:22');
        $number  = $subject->toNumber();

        self::assertTrue($subject->equals(new IPv6Address($number)));
    }

    public function testShouldIdentifyNullAddress(): void
    {
        self::assertTrue(IPv6Address::fromString('::')->isNull());
        self::assertFalse(IPv6Address::fromString('fe80::a65:78:0:22')->isNull());
    }

    public function testShouldBeJsonSerializable(): void
    {
        $subject = IPv6Address::fromString('fe80::a65:78:0:22');
        $json    = json_encode($subject);

        self::assertSame(
            $subject->toString(),
            IPv6Address::fromString(json_decode($json))->toString(),
        );
    }
}
