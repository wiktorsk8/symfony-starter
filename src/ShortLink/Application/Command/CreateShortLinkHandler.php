<?php

declare(strict_types=1);

namespace App\ShortLink\Application\Command;

use App\ShortLink\Application\Events\ShortLinkCreated;
use App\ShortLink\Application\Service\LinkShortenerService;
use App\ShortLink\Infrastructure\Cache\Repositories\ShortLinkCacheRepository;
use App\ShortLink\Infrastructure\Doctrine\Entity\ShortLink;
use App\ShortLink\Infrastructure\Doctrine\Repository\ShortLinkRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateShortLinkHandler
{
    public function __construct(
        protected ShortLinkRepository $repository,
        protected LinkShortenerService $linkShortenerService,
        protected ShortLinkCacheRepository $cacheRepository,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function handle(CreateShortLink $command): void
    {
        $count = $this->repository->getCountWithLock();

        $count++;

        $slug = $this->linkShortenerService->generateSlug($count);
        $shortLink = new ShortLink();

        $shortLink->setId($command->id);
        $shortLink->setUrl($command->url);
        $shortLink->setSlug($slug);
        $shortLink->setCount($count);

        $this->repository->save($shortLink, true);

        $this->eventDispatcher->dispatch(new ShortLinkCreated(
            id: $shortLink->getId(),
            slug: $shortLink->getSlug()
        ));
    }
}
