<?php

declare(strict_types=1);

namespace App\ShortLink\Application\Queries;

use App\ShortLink\Application\Events\ShortLinkAccessed;
use App\ShortLink\Application\Exceptions\AccessUrlException;
use App\ShortLink\Application\Exceptions\GetUrlException;
use App\ShortLink\Application\Exceptions\ShortLinkNotFoundException;
use App\ShortLink\Application\Service\AccessUrlService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class GetUrlQuery
{
    public function __construct(
        protected AccessUrlService         $accessUrlService,
        protected EventDispatcherInterface $eventDispatcher
    )
    {
    }

    /**
     * @throws GetUrlException
     */
    public function execute(string $slug): ?string
    {
        try {
            $url = $this->accessUrlService->accessUrl($slug);
        } catch (AccessUrlException|InvalidArgumentException $e) {
            throw new GetUrlException($e->getMessage(), $e->getCode(), $e);
        }


        $this->eventDispatcher->dispatch(
            new ShortLinkAccessed($slug)
        );

        return $url;
    }
}
