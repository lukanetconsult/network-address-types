<?php

declare(strict_types=1);

namespace LUKA\Network;

/**
 * @psalm-immutable
 */
interface Network extends Address
{
    public function getRangeMinAddress(): CIDRAddress;

    public function getRangeMaxAddress(): CIDRAddress;

    public function containsAddress(IPAddress $address): bool;

    public function toCidrAddress(): CIDRAddress;
}
