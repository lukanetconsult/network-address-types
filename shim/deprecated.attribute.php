<?php

declare(strict_types=1);

#[Attribute()]
final readonly class Deprecated
{
    public function __construct(
        public string|null $message,
        public string|null $since,
    ) {
    }
}
