<?php

declare(strict_types=1);

namespace App\ShortLink\Infrastructure\Symfony\Request;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

final readonly class CreateShortLink
{
    public function __construct(
        public mixed $url,
    ) {
    }
    public static function fromHttp(Request $request): self
    {
        $data = json_decode($request->getContent(), true);

        return new self($data['url'] ?? '');
    }
    public function toCommand(): \App\ShortLink\Application\Command\CreateShortLink
    {
        return new \App\ShortLink\Application\Command\CreateShortLink(
            id: Uuid::uuid4()->toString(),
            url: $this->url,
        );
    }
}
