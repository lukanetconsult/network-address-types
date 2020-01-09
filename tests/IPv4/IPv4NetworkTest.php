<?php

declare(strict_types=1);

namespace LUKATest\Network\IPv4;

use LUKA\Network\IPv4\CIDRv4Address;
use LUKA\Network\IPv4\IPv4Address;
use LUKA\Network\IPv4\IPv4Network;
use PHPUnit\Framework\TestCase;

class IPv4NetworkTest extends TestCase
{
    public function testShouldNormalizeCidrAddressToPrefix(): void
    {
        $expected = '88.154.76.0/24';
        $subject  = new IPv4Network(CIDRv4Address::fromString('88.154.76.23/24'));

        self::assertSame($expected, $subject->toString());
        self::assertEquals($expected, $subject->toCidrAddress()->toString());
    }

    public function testShouldContainAddressesWithinTheSamePrefix(): void
    {
        self::assertTrue(
            (new IPv4Network(CIDRv4Address::fromString('88.154.76.23/24')))
                ->containsAddress(IPv4Address::fromString('88.154.76.23'))
        );
    }

    public function provideRangeTestData(): iterable
    {
        $data = [
            '88.154.76.23/24' => ['88.154.76.1/24', '88.154.76.254/24'],
            '88.154.76.0/24' => ['88.154.76.1/24', '88.154.76.254/24'],
            '88.154.76.23/8' => ['88.0.0.1/8', '88.255.255.254/8'],
            '88.154.76.23/32' => ['88.154.76.23/32', '88.154.76.23/32'],
            '88.154.76.23/31' => ['88.154.76.23/31', '88.154.76.23/31'],
        ];

        foreach ($data as $input => $range) {
            yield $input => [$input, ...$range];
        }
    }

    /** @dataProvider provideRangeTestData */
    public function testShouldProvideCorrectAddressRange(string $input, string $min, string $max): void
    {
        $subject = new IPv4Network(CIDRv4Address::fromString($input));

        self::assertSame($min, $subject->getRangeMinAddress()->toString());
        self::assertSame($max, $subject->getRangeMaxAddress()->toString());
    }
}
