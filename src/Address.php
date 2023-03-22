<?php

declare(strict_types=1);

namespace LUKA\Network;

/** @psalm-immutable */
interface Address
{
    public function toString(): string;

    public function equals(Address $other): bool;
}
