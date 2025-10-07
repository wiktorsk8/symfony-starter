<?php

declare(strict_types=1);

namespace App\ShortLink\Application\Queries;

use App\ShortLink\Application\Exceptions\GetUrlException;
use App\ShortLink\Infrastructure\Cache\Repositories\ShortLinkCacheRepository;
use Psr\Cache\InvalidArgumentException;


class GetUrlQuery
{
    public function __construct(
        protected ShortLinkCacheRepository $shortLinkCacheRepository,
    ) {
    }

    /**
     * @throws GetUrlException
     */
    public function execute(string $slug): ?string
    {
        try {
            $url = $this->shortLinkCacheRepository->cacheUrl($slug);
        } catch (InvalidArgumentException $e) {
            throw new GetUrlException($e->getMessage(), $e->getCode(), $e);
        }

        return $url;
    }
}
