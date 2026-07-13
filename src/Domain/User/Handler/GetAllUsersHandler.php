<?php

declare(strict_types=1);

namespace App\Domain\User\Handler;

use App\Domain\User\Query\GetAllUsers;
use App\Domain\User\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetAllUsersHandler
{
    public function __construct(private UserRepository $userRepository) {}

    /**
     * @return User[]
     */
    public function __invoke(GetAllUsers $query): array
    {
        return $this->userRepository->all();
    }
}
