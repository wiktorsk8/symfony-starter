<?php

declare(strict_types=1);

namespace App\ShortLink\Application\Queries;

use App\ShortLink\Application\Exceptions\GetUrlException;
use App\ShortLink\Infrastructure\Doctrine\Repository\ShortLinkRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class GetUrlQuery
{
    public function __construct(
        protected CacheInterface $cache,
        protected ShortLinkRepository $repository,
    ) {
    }

    /**
     * @throws GetUrlException
     */
    public function execute(string $slug): ?string
    {
        try {
           $url = $this->getUrl($slug);
        } catch (InvalidArgumentException $e) {
            throw new GetUrlException($e->getMessage(), $e->getCode(), $e);
        }

        return $url;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getUrl(string $slug): ?string
    {
        return $this->cache->get($slug, function (ItemInterface $item) use ($slug) {
            $item->expiresAfter(3600);
            $shortLink = $this->repository->findBySlug($slug);
            return $shortLink?->getUrl();
        });
    }
}
