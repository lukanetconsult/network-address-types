<?php

declare(strict_types=1);

namespace LUKATest\Network\IPv4;

use InvalidArgumentException;
use LUKA\Network\Address;
use LUKA\Network\IPv4\CIDRv4Address;
use LUKA\Network\IPv4\IPv4Address;
use LUKA\Network\IPv6\IPv6Address;
use PHPUnit\Framework\TestCase;

use function json_decode;
use function json_encode;

class IPv4AddressTest extends TestCase
{
    public function provideValidIps(): iterable
    {
        $list = [
            '0.0.0.0',
            '255.255.255.255',
            '127.0.0.1',
            '10.0.5.153',
            '86.134.56.253',
        ];

        foreach ($list as $ip) {
            yield $ip => [$ip];
        }
    }

    public function provideInvalidIps(): iterable
    {
        $list = [
            'a.7.s.s',
            'localhost',
            '127.0.0',
            '10.0.5.256',
            '10.0.256.2',
            '10.256.0.2',
            '256.0.0.2',
            '10.896.0.2',
            '86.1340.56.263',
            '',
            '127001',
        ];

        foreach ($list as $ip) {
            yield 'value: ' . $ip => [$ip];
        }
    }

    /** @dataProvider provideValidIps */
    public function testShouldAcceptValidAddresses(string $fixture): void
    {
        $subject = IPv4Address::fromString($fixture);
        self::assertSame($fixture, $subject->toString());
    }

    /** @dataProvider provideInvalidIps */
    public function testShouldThrowOnInvalidAddresses(string $fixture): void
    {
        $this->expectException(InvalidArgumentException::class);
        IPv4Address::fromString($fixture);
    }

    public function testShouldCompareEquality(): void
    {
        $first  = IPv4Address::fromString('192.168.55.7');
        $second = IPv4Address::fromString('192.168.55.7');

        self::assertNotSame($first, $second);
        self::assertTrue($first->equals($second));
    }

    public function provideInequalityTestData(): iterable
    {
        return [
            'different address' => ['127.0.0.1', IPv4Address::fromString('127.0.0.2')],
            'different type' => ['127.0.0.1', IPv6Address::fromString('::1')],
            'cidr' => ['127.0.0.1', CIDRv4Address::fromString('127.0.0.1/8')],
        ];
    }

    /** @dataProvider provideInequalityTestData */
    public function testShouldNotMatchInequality(string $subject, Address $other): void
    {
        self::assertFalse(IPv4Address::fromString($subject)->equals($other));
    }

    public function testShouldConvertToInteger(): void
    {
        $int = IPv4Address::fromString('127.0.0.1')->toInt();

        self::assertSame(0x7f000001, $int);
        self::assertSame(
            '127.0.0.1',
            (new IPv4Address($int))->toString(),
        );
    }

    public function testShouldExportBinaryValue(): void
    {
        $expected = '255.0.0.5';
        $bytes    = IPv4Address::fromString($expected)->toByteString();

        self::assertNotSame($expected, $bytes);
        self::assertSame($expected, IPv4Address::fromByteString($bytes)->toString());
    }

    public function testShouldIdentifyNullAddress(): void
    {
        self::assertTrue(IPv4Address::fromString('0.0.0.0')->isNull());
        self::assertFalse(IPv4Address::fromString('127.0.0.1')->isNull());
    }

    public function testShouldBeJsonSerializable(): void
    {
        $subject = IPv4Address::fromString('192.168.0.33');
        $json    = json_encode($subject);

        self::assertTrue(
            $subject->equals(
                IPv4Address::fromString(json_decode($json)),
            ),
        );
    }
}
