<?php

namespace App\ShortLink\Application\Command;

use App\Shared\Application\Command\Command;

class TrackShortLinkClick implements Command
{
    public function __construct(
        public string $slug,
        public ?string $userIdentifier,
        public string $userAgent,
        public string $ip,
    ) {
    }
}
