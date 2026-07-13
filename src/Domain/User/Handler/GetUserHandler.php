<?php

declare(strict_types=1);

namespace App\Domain\User\Handler;

use App\Domain\User\Query\GetUser;
use App\Domain\User\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetUserHandler
{
    public function __construct(private UserRepository $userRepository) {}

    public function __invoke(GetUser $query): User
    {
        return $this->userRepository->withIdStrict($query->id);
    }
}
