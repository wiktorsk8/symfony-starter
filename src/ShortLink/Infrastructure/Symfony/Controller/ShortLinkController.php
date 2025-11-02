<?php

declare(strict_types=1);

namespace App\ShortLink\Infrastructure\Symfony\Controller;

use App\Shared\Infrastructure\Symfony\Messenger\CommandBus;
use App\ShortLink\Application\Command\TrackShortLinkClick;
use App\ShortLink\Application\Exceptions\GetUrlException;
use App\ShortLink\Application\Queries\GetShortLink;
use App\ShortLink\Application\Queries\GetUrlQuery;
use App\ShortLink\Infrastructure\Symfony\Request\CreateShortLink;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class ShortLinkController extends AbstractController
{
    /**
     * @throws Throwable
     */
    #[Route('/api/short_links', methods: ['POST'])]
    public function createShortLink(
        Request $request,
        CommandBus $commandBus,
    ): JsonResponse {
        $command = CreateShortLink::fromHttp($request)->toCommand();

        try {
            $commandBus->dispatch($command);
        } catch (\Exception|ExceptionInterface $e) {
            return new JsonResponse([
                "message" => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'id' => $command->id,
        ]);
    }

    #[Route('/api/short_links/{shortLinkId}', methods: ['GET'])]
    public function getShortLink(
        string $shortLinkId,
        GetShortLink $getShortLinkQuery
    ): JsonResponse {
        $dto = $getShortLinkQuery->execute($shortLinkId);
        if (!$dto) {
            return new JsonResponse([
                'message' => 'Not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($dto->toArray());
    }

    /**
     * @throws Throwable
     */
    #[Route('/{slug}', methods: ['GET'])]
    public function getUrl(
        Request $request,
        string $slug,
        GetUrlQuery $getUrlQuery,
        CommandBus $commandBus,
    ): Response {
        try {
            $url = $getUrlQuery->execute($slug);
        } catch (GetUrlException $e) {
            return new JsonResponse(["message" => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (!$url) {
            return new JsonResponse(["message" => "Not found"], Response::HTTP_NOT_FOUND);
        }

        try {
            $commandBus->dispatch(new TrackShortLinkClick(
                slug: $slug,
                userIdentifier: $this->getUser()?->getUserIdentifier() ?? null,
                userAgent: $request->headers->get('User-Agent'),
                ip: $request->getClientIp(),
            ));
        } catch (ExceptionInterface $e) {
            return new JsonResponse(["message" => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new RedirectResponse($url);
    }
}
