<?php

namespace App\ShortLink\Infrastructure\Cache\Repositories;

use App\ShortLink\Infrastructure\Doctrine\Repository\ShortLinkRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ShortLinkCacheRepository
{
    public function __construct(
        protected CacheInterface $cache,
        protected ShortLinkRepository $repository
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function cacheUrl(string $slug): string
    {
        return $this->cache->get($slug, function (ItemInterface $item) use ($slug): string {
            $item->expiresAfter(3600);
            $shortLink = $this->repository->findBySlug($slug);
            return $shortLink?->getUrl();
        });
    }
}
