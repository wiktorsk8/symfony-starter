<?php

declare(strict_types=1);

namespace App\ShortLink\Application\Service;

use App\ShortLink\Application\Exceptions\CannotAccessUrlException;
use App\ShortLink\Infrastructure\Cache\Repositories\ShortLinkCacheRepository;
use App\ShortLink\Infrastructure\Doctrine\Entity\ShortLink;
use App\ShortLink\Infrastructure\Doctrine\Repository\ShortLinkRepository;
use App\ShortLink\Infrastructure\Doctrine\Repository\WhiteListedUserRepository;
use Doctrine\DBAL\LockMode;
use Psr\Cache\InvalidArgumentException;

class AccessUrlService
{
    public function __construct(
        protected ShortLinkCacheRepository  $cacheRepository,
        protected WhiteListedUserRepository $whiteListedUserRepository,
        protected ShortLinkRepository $shortLinkRepository,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws CannotAccessUrlException
     */
    public function accessUrl(string $slug, ?string $userIdentifier): ?string
    {
        $shortLink = $this->cacheRepository->cacheUrl($slug);
        if (null === $shortLink) {
            return null;
        }

        if ($shortLink->getIsWhiteListed()) {
            if (null === $userIdentifier || !$this->onWhiteList($shortLink, $userIdentifier)) {
                throw new CannotAccessUrlException(message: "dsad");
            }
        }

        if (null !== $shortLink->getAccessLimit()) {
            $this->checkAccessLimits($shortLink);
        }

        return $shortLink->getUrl();
    }

    /**
     * @throws CannotAccessUrlException
     */
    private function checkAccessLimits(ShortLink $shortLink): void
    {
        $shortLink = $this->shortLinkRepository->find(
            id: $shortLink->getId(),
            lockMode: LockMode::PESSIMISTIC_WRITE
        );

        if ($shortLink->getAccessLimit() <= $shortLink->getAccessCounter()) {
            throw new CannotAccessUrlException(message: "dsad");
        }

        $this->shortLinkRepository->incrementAccessCounter($shortLink);
    }


    private function onWhiteList(ShortLink $shortLink, string $userIdentifier): bool
    {
        return (bool)$this->whiteListedUserRepository->getWhiteListedUser($shortLink->getId(), $userIdentifier);
    }
}
