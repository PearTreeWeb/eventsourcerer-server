<?php

declare(strict_types=1);

namespace App\Domain\User\Handler;

use App\Domain\User\Command\UpdateUser;
use App\Domain\User\Model\UserId;
use App\Domain\User\Repository\UserRepository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private ClockInterface $clock
    ) {}

    public function __invoke(UpdateUser $command): void
    {
        $user = $command->user;

        $updatedUser = $this
            ->userRepository
            ->withIdStrict($user->id)
            ->setEmail($user->emailAddress->toString())
            ->setRole($user->role);

        $this->userRepository->update($updatedUser, $this->clock->now());
    }
}
