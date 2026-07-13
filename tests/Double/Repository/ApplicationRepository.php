<?php

declare(strict_types=1);

namespace App\Tests\Double\Repository;

use App\Domain\Application\Model\ApplicationName;
use App\Domain\Application\Repository\ApplicationRepository as ApplicationRepositoryInterface;
use App\Entity\Application;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;

final readonly class ApplicationRepository implements ApplicationRepositoryInterface
{
    public const string APPLICATION_ID = '6b0db8ad-abcb-4e00-9645-e2a2e7916acf';
    private const string APPLICATION_NAME = 'Some Application Name';

    public function all(): iterable
    {
        return [];
    }

    public function create(Application $application): Application
    {
        return $application;
    }

    public function byId(ApplicationId $id): ?Application
    {
        return $this->byIdStrict($id);
    }

    public function byIdStrict(ApplicationId $id): Application
    {
        return self::mockApplication();
    }

    public function update(Application $application): Application
    {
        return $application;
    }

    private static function mockApplication(): Application
    {
        return Application::create(
            ApplicationId::fromString(self::APPLICATION_ID),
            ApplicationName::fromString(self::APPLICATION_NAME),
            new \DateTimeImmutable()
        );
    }
}
