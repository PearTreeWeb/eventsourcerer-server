<?php

namespace App\Domain\User\Handler;

use App\Domain\User\Command\DeleteUser;
use App\Domain\User\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteUserHandler
{
    public function __construct(private UserRepository $userRepository) {}

    public function __invoke(DeleteUser $command): void
    {
        $this->userRepository->delete($command->userId);
    }
}