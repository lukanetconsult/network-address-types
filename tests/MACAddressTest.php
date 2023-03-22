<?php

declare(strict_types=1);

namespace LUKATest\Network;

use InvalidArgumentException;
use LUKA\Network\Address;
use LUKA\Network\IPv6\IPv6Address;
use LUKA\Network\MACAddress;
use PHPUnit\Framework\TestCase;

use function json_decode;
use function json_encode;

class MACAddressTest extends TestCase
{
    public function testShouldCheckForLocalAddress(): void
    {
        self::assertTrue(MACAddress::fromString('02:00:00:00:00:00')->isLocal());
        self::assertFalse(MACAddress::fromString('dc:0e:a1:6e:08:c2')->isLocal());
    }

    public function testShouldExportTheVendorId(): void
    {
        self::assertSame('dc0ea1', MACAddress::fromString('dc:0e:a1:6e:08:c2')->getVendorID());
    }

    public function provideInvalidStringInput(): iterable
    {
        return [
            'empty string' => [''],
            'too many bytes' => ['02:75:00:56:4f:a8:c2'],
            'invalid characters' => ['02:75:00:z6:4f:a8'],
            'arbitary string' => ['hello world'],
        ];
    }

    /** @dataProvider provideInvalidStringInput */
    public function testShouldThrowWhenConstructedFromInvalidStringAddress(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        MACAddress::fromString($input);
    }

    public function testShouldStringifyColonSeparatedNotation(): void
    {
        self::assertSame('dc:0e:a1:6e:08:c2', MACAddress::fromString('dc0ea16e08c2')->toString());
        self::assertSame('dc:0e:a1:6e:08:c2', MACAddress::fromString('dc-0e-a1-6e-08-c2')->toString());
    }

    public function testShouldIdentifyBroadcastAddress(): void
    {
        self::assertTrue(MACAddress::fromString('ff:ff:ff:ff:ff:ff')->isBroadCast());
        self::assertFalse(MACAddress::fromString('dc:0e:a1:6e:08:c2')->isBroadCast());
    }

    public function testShouldBeJsonSerializable(): void
    {
        $subject = MACAddress::generateRandomAddress();
        $json    = json_encode($subject);

        self::assertSame(
            $subject->toString(),
            MACAddress::fromString(json_decode($json))->toString(),
        );
    }

    public function testShouldGenerateRandomAddresses(): void
    {
        $first  = MACAddress::generateRandomAddress();
        $second = MACAddress::generateRandomAddress();

        self::assertNotSame(
            $first->toString(),
            $second->toString(),
        );
    }

    public function testShouldGenerateRandomAddressesWithPrefix(): void
    {
        $prefix = '200000';
        $first  = MACAddress::generateRandomAddress($prefix);
        $second = MACAddress::generateRandomAddress($prefix);

        self::assertNotSame(
            $first->toString(),
            $second->toString(),
        );

        self::assertStringStartsWith('20:00:00', $first->toString());
        self::assertStringStartsWith('20:00:00', $second->toString());
    }

    public function testShouldExportCorrectByteString(): void
    {
        self::assertSame(
            "\x20\x00\x00\x00\x00\x01",
            MACAddress::fromString('20:00:00:00:00:01')->toByteString(),
        );
    }

    public function testShouldCompareEquality(): void
    {
        $first  = MACAddress::fromString('20:00:00:00:00:01');
        $second = MACAddress::fromString('20:00:00:00:00:01');

        self::assertNotSame($first, $second);
        self::assertTrue($first->equals($second));
    }

    public function provideInequalityTestData(): iterable
    {
        return [
            'different address' => ['20:00:00:00:00:01', MACAddress::fromString('20:00:00:00:00:02')],
            'different type' => ['20:00:00:00:00:01', IPv6Address::fromString('::1')],
        ];
    }

    /** @dataProvider provideInequalityTestData */
    public function testShouldNotMatchInequality(string $subject, Address $other): void
    {
        self::assertFalse(MACAddress::fromString($subject)->equals($other));
    }
}
