<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Symfony\Controller;

use App\Shared\Infrastructure\Symfony\Messenger\CommandBus;
use App\User\Infrastructure\Symfony\DTOs\RegisterUserDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

class RegisterController extends AbstractController
{
    public function register(
        #[MapRequestPayload] RegisterUserDTO $DTO,
        CommandBus $commandBus,
    ): JsonResponse {
        try {
            $commandBus->dispatch($DTO->toCommand());
        } catch (ExceptionInterface $e) {
            return new JsonResponse([
                "message" => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
