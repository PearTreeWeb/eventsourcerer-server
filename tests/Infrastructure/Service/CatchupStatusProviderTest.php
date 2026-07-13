<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Infrastructure\CatchupStatus;
use App\Infrastructure\Service\CatchupStatusProvider;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CatchupStatusProviderTest extends TestCase
{
    private CatchupStatusProvider $catchupStatusProvider;
    private ArrayAdapter $cache;

    protected function setUp(): void
    {
        $this->cache = new ArrayAdapter();
        $this->catchupStatusProvider = new CatchupStatusProvider($this->cache);
    }

    #[Test]
    public function itReturnsStoppedStatusForUnknownWorker(): void
    {
        $workerId = WorkerId::fromString('test-worker');

        $status = $this->catchupStatusProvider->statusFor($workerId);

        $this->assertEquals(CatchupStatus::Stopped, $status);
    }

    #[Test]
    public function itCanSetAndRetrieveRunningStatus(): void
    {
        $workerId = WorkerId::fromString('test-worker');

        $this->catchupStatusProvider->setAsRunningFor($workerId);

        $status = $this->catchupStatusProvider->statusFor($workerId);
        $this->assertEquals(CatchupStatus::Running, $status);
        $this->assertFalse($this->catchupStatusProvider->isPausedFor($workerId));
    }

    #[Test]
    public function itCanSetAndRetrievePausedStatus(): void
    {
        $workerId = WorkerId::fromString('test-worker');

        $this->catchupStatusProvider->setAsPausedFor($workerId);

        $status = $this->catchupStatusProvider->statusFor($workerId);
        $this->assertEquals(CatchupStatus::Paused, $status);
        $this->assertTrue($this->catchupStatusProvider->isPausedFor($workerId));
    }

    #[Test]
    public function itCanChangeStatusFromRunningToPaused(): void
    {
        $workerId = WorkerId::fromString('test-worker');

        $this->catchupStatusProvider->setAsRunningFor($workerId);
        $this->assertEquals(CatchupStatus::Running, $this->catchupStatusProvider->statusFor($workerId));

        $this->catchupStatusProvider->setAsPausedFor($workerId);
        $this->assertEquals(CatchupStatus::Paused, $this->catchupStatusProvider->statusFor($workerId));
    }

    #[Test]
    public function itCanChangeStatusFromPausedToRunning(): void
    {
        $workerId = WorkerId::fromString('test-worker');

        $this->catchupStatusProvider->setAsPausedFor($workerId);
        $this->assertEquals(CatchupStatus::Paused, $this->catchupStatusProvider->statusFor($workerId));

        $this->catchupStatusProvider->setAsRunningFor($workerId);
        $this->assertEquals(CatchupStatus::Running, $this->catchupStatusProvider->statusFor($workerId));
    }

    #[Test]
    public function itMaintainsSeparateStatusForDifferentWorkers(): void
    {
        $workerId1 = WorkerId::fromString('worker-1');
        $workerId2 = WorkerId::fromString('worker-2');

        $this->catchupStatusProvider->setAsRunningFor($workerId1);
        $this->catchupStatusProvider->setAsPausedFor($workerId2);

        $this->assertEquals(CatchupStatus::Running, $this->catchupStatusProvider->statusFor($workerId1));
        $this->assertEquals(CatchupStatus::Paused, $this->catchupStatusProvider->statusFor($workerId2));
        $this->assertFalse($this->catchupStatusProvider->isPausedFor($workerId1));
        $this->assertTrue($this->catchupStatusProvider->isPausedFor($workerId2));
    }
}
