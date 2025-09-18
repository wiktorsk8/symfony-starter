<?php

namespace App\ShortLink\Application\Queries;

use App\ShortLink\Application\Queries\Result\ShortLinkDTO;
use App\ShortLink\Infrastructure\Doctrine\Repository\ShortLinkRepository;

class GetShortLinkQuery
{
    public function __construct(
        private ShortLinkRepository $repository
    ) {
    }

    public function execute(string $id): ?ShortLinkDTO
    {
        $shortLink = $this->repository->find($id);

        if (null === $shortLink) {
            return null;
        }

        return new ShortLinkDTO(
            id: $shortLink->getId(),
            slug: $shortLink->getSlug(),
            url: $shortLink->getUrl(),
        );
    }
}
