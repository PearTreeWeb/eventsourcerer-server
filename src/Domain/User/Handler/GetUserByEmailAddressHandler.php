<?php

declare(strict_types=1);

namespace App\Domain\User\Handler;

use App\Domain\User\Query\GetUserByEmailAddress;
use App\Domain\User\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetUserByEmailAddressHandler
{
    public function __construct(private UserRepository $repository) {}

    public function __invoke(GetUserByEmailAddress $query): User
    {
        return $this->repository->withEmailAddressStrict($query->emailAddress);
    }
}
