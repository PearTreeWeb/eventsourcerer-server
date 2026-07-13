<?php

declare(strict_types=1);

namespace App\Tests\Domain\Application\Handler;

use App\Domain\Application\Command\RegisterApplication;
use App\Domain\Application\Handler\RegisterApplicationHandler;
use App\Domain\Application\Model\ApplicationName;
use App\Domain\Application\Repository\ApplicationRepository;
use App\Domain\Application\Service\CertificateGenerator;
use App\Entity\Application;
use App\Tests\Double\Id;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterApplicationHandlerTest extends TestCase
{
    private RegisterApplicationHandler $handler;
    private MockObject&ApplicationRepository $applicationRepository;
    private MockObject&CertificateGenerator $certificateGenerator;
    private MockObject&ClockInterface $clock;

    protected function setUp(): void
    {
        $this->applicationRepository = $this->createMock(ApplicationRepository::class);
        $this->certificateGenerator = $this->createMock(CertificateGenerator::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->handler = new RegisterApplicationHandler(
            $this->applicationRepository,
            $this->certificateGenerator,
            $this->clock,
            $this->createStub(UserPasswordHasherInterface::class),
        );
    }

    #[Test]
    public function itRegistersApplication(): void
    {
        $applicationId = Id::applicationId();
        $applicationName = Id::applicationName();
        $hostname = 'custom.host';
        $now = new \DateTimeImmutable();

        $command = new RegisterApplication($applicationId, $applicationName, $hostname);

        $this->clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $this->applicationRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (Application $application) use ($applicationId, $applicationName, $hostname, $now) {
                return $application->id()->toString() === $applicationId->toString()
                    && $application->name() === $applicationName->toString()
                    && $application->hostname() === $hostname
                    && $application->createdAt() == $now;
            }));

        $this->certificateGenerator
            ->expects($this->once())
            ->method('generateForApplication')
            ->with($this->callback(function (Application $application) use ($applicationId, $applicationName, $hostname) {
                return $application->id()->toString() === $applicationId->toString()
                    && $application->name() === $applicationName->toString()
                    && $application->hostname() === $hostname;
            }));

        $this->handler->__invoke($command);
    }

    #[Test]
    public function itCanBeConstructed(): void
    {
        $handler = new RegisterApplicationHandler(
            $this->applicationRepository,
            $this->certificateGenerator,
            $this->clock,
            $this->createStub(UserPasswordHasherInterface::class),
        );

        $this->assertInstanceOf(RegisterApplicationHandler::class, $handler);
    }

    #[Test]
    public function itHandlesDifferentApplicationIds(): void
    {
        $applicationId1 = ApplicationId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $applicationName = Id::applicationName();
        $now = new \DateTimeImmutable();

        $command = new RegisterApplication($applicationId1, $applicationName);

        $this->clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $this->applicationRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (Application $application) use ($applicationId1, $applicationName, $now) {
                return $application->id()->toString() === $applicationId1->toString()
                    && $application->name() === $applicationName->toString()
                    && $application->createdAt() == $now;
            }));

        $this->certificateGenerator
            ->expects($this->once())
            ->method('generateForApplication');

        $this->handler->__invoke($command);
    }

    #[Test]
    public function itHandlesDifferentApplicationNames(): void
    {
        $applicationId = Id::applicationId();
        $applicationName = ApplicationName::fromString('Different App');
        $now = new \DateTimeImmutable();

        $command = new RegisterApplication($applicationId, $applicationName);

        $this->clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $this->applicationRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (Application $application) use ($applicationId, $applicationName, $now) {
                return $application->id()->toString() === $applicationId->toString()
                    && $application->name() === $applicationName->toString()
                    && $application->createdAt() == $now;
            }));

        $this->certificateGenerator
            ->expects($this->once())
            ->method('generateForApplication');

        $this->handler->__invoke($command);
    }

    #[Test]
    public function itHandlesEmptyHostname(): void
    {
        $applicationId = Id::applicationId();
        $applicationName = Id::applicationName();
        $hostname = null;
        $now = new \DateTimeImmutable();

        $command = new RegisterApplication($applicationId, $applicationName, $hostname);

        $this->clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $this->applicationRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (Application $application) use ($applicationId, $applicationName, $now) {
                return $application->id()->toString() === $applicationId->toString()
                    && $application->name() === $applicationName->toString()
                    && $application->hostname() === null
                    && $application->createdAt() == $now;
            }));

        $this->certificateGenerator
            ->expects($this->once())
            ->method('generateForApplication');

        $this->handler->__invoke($command);
    }
}
