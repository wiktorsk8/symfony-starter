<?php

declare(strict_types=1);

namespace App\ShortLink\Infrastructure\Symfony\Controller;

use App\ShortLink\Application\Command\CreateShortLinkHandler;
use App\ShortLink\Application\Exceptions\GetUrlException;
use App\ShortLink\Application\Queries\GetShortLinkQuery;
use App\ShortLink\Application\Queries\GetUrlQuery;
use App\ShortLink\Infrastructure\Symfony\Request\CreateShortLink;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShortLinkController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    #[Route('/api/short_links', methods: ['POST'])]
    public function createShortLink(
        Request $request,
        CreateShortLinkHandler $createShortLinkHandler,
    ): JsonResponse {
        $command = CreateShortLink::fromHttp($request)->toCommand();

        $this->em->beginTransaction();
        try {
            $createShortLinkHandler->handle($command);
        } catch (\Exception $e) {
            $this->em->rollback();
            return new JsonResponse([
                "message" => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->em->commit();

        return new JsonResponse([
            'id' => $command->id,
        ]);
    }

    #[Route('/api/short_links/{shortLinkId}', methods: ['GET'])]
    public function getShortLink(
        string $shortLinkId,
        GetShortLinkQuery $getShortLinkQuery
    ): JsonResponse {
        $dto = $getShortLinkQuery->execute($shortLinkId);
        if (!$dto) {
            return new JsonResponse([
                'message' => 'Not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($dto->toArray());
    }

    #[Route('/{slug}', methods: ['GET'])]
    public function getUrl(
        string $slug,
        GetUrlQuery $getUrlQuery,
    ): Response {
        try {
            $url = $getUrlQuery->execute($slug);
        } catch (GetUrlException $e) {
            return new JsonResponse(["message" => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (!$url) {
            return new JsonResponse(["message" => "Not found"], Response::HTTP_NOT_FOUND);
        }

        return new RedirectResponse($url);
    }
}
