<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Handler;

use App\Domain\User\Command\UpdateUser;
use App\Domain\User\Handler\UpdateUserHandler;
use App\Domain\User\Repository\UserRepository;
use App\Tests\Double\ValueObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class UpdateUserHandlerTest extends TestCase
{
    private MockClock $clock;

    public function setUp(): void
    {
        $this->clock = new MockClock();
    }

    public function testItUpdatesAUser(): void
    {
        $userRepository = $this->createMock(UserRepository::class);

        $userRepository->expects($this->once())->method('update');

        $handler = new UpdateUserHandler($userRepository, $this->clock);

        $handler(new UpdateUser(ValueObject::user()));
    }
}
