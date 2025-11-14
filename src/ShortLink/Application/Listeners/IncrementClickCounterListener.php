<?php

declare(strict_types=1);

namespace App\ShortLink\Application\Listeners;

use App\ShortLink\Application\Events\ShortLinkAccessed;
use App\ShortLink\Infrastructure\Doctrine\Repository\ShortLinkRepository;

class IncrementClickCounterListener
{
    public function __construct(
        protected ShortLinkRepository $repository,
    ) {
    }

    public function onShortLinkAccessed(ShortLinkAccessed $event): void
    {
        $shortLink = $this->repository->find($event->id);
        $shortLink->incrementClickCounter();
        $this->repository->save($shortLink, true);
    }
}
