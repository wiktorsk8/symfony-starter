<?php

declare(strict_types=1);

namespace App\ShortLink\Application\Command;

readonly class CreateShortLink
{
    public function __construct(
        public string $id,
        public string $url,
    ) {
    }
}
