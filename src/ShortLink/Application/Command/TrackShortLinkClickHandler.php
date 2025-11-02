<?php

namespace App\ShortLink\Application\Command;

use App\Shared\Application\Command\CommandHandler;
use App\ShortLink\Application\Exceptions\ShortLinkNotFoundException;
use App\ShortLink\Infrastructure\Doctrine\Repository\ShortLinkRepository;

class TrackShortLinkClickHandler implements CommandHandler
{
    public function __construct(
        protected ShortLinkRepository $shortLinkRepository,
    ) {
    }

    /**
     * @throws ShortLinkNotFoundException
     */
    public function __invoke(TrackShortLinkClick $command): void
    {
        $shortLink = $this->shortLinkRepository->findBySlug($command->slug);
        if (null === $shortLink) {
            throw new ShortLinkNotFoundException();
        }


    }
}
