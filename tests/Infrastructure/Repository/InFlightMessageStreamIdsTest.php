<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Infrastructure\Repository\InFlightMessageStreamIds;
use App\Tests\Double\Id;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class InFlightMessageStreamIdsTest extends TestCase
{
    private InFlightMessageStreamIds $inFlightMessageStreamIds;

    protected function setUp(): void
    {
        $this->inFlightMessageStreamIds = new InFlightMessageStreamIds(new ArrayAdapter());
    }

    #[Test]
    public function itReturnsEmptyArrayForApplicationWithNoInFlightMessages(): void
    {
        $applicationId = Id::applicationId();

        $inFlightStreamIds = $this->inFlightMessageStreamIds->for($applicationId);

        $this->assertEmpty($inFlightStreamIds);
    }

    #[Test]
    public function itCanAddAndRetrieveInFlightStreamIds(): void
    {
        $applicationId = Id::applicationId();
        $streamId = Id::streamId();

        $this->inFlightMessageStreamIds->addFor($applicationId, $streamId);

        $inFlightStreamIds = $this->inFlightMessageStreamIds->for($applicationId);

        $this->assertCount(1, $inFlightStreamIds);
        $this->assertContains($streamId->toString(), $inFlightStreamIds);
    }

    #[Test]
    public function itCanAddMultipleStreamIdsForSameApplication(): void
    {
        $applicationId = Id::applicationId();
        $streamId1 = Id::streamId();
        $streamId2 = Id::streamId2();

        $this->inFlightMessageStreamIds->addFor($applicationId, $streamId1);
        $this->inFlightMessageStreamIds->addFor($applicationId, $streamId2);

        $inFlightStreamIds = $this->inFlightMessageStreamIds->for($applicationId);

        $this->assertCount(2, $inFlightStreamIds);
        $this->assertContains($streamId1->toString(), $inFlightStreamIds);
        $this->assertContains($streamId2->toString(), $inFlightStreamIds);
    }

    #[Test]
    public function itDoesNotDuplicateStreamIdsWhenAddedTwice(): void
    {
        $applicationId = Id::applicationId();
        $streamId = Id::streamId();

        $this->inFlightMessageStreamIds->addFor($applicationId, $streamId);
        $this->inFlightMessageStreamIds->addFor($applicationId, $streamId);

        $inFlightStreamIds = $this->inFlightMessageStreamIds->for($applicationId);

        $this->assertCount(1, $inFlightStreamIds);
        $this->assertContains($streamId->toString(), $inFlightStreamIds);
    }

    #[Test]
    public function itCanRemoveInFlightStreamIds(): void
    {
        $applicationId = Id::applicationId();
        $streamId1 = Id::streamId();
        $streamId2 = Id::streamId2();

        $this->inFlightMessageStreamIds->addFor($applicationId, $streamId1);
        $this->inFlightMessageStreamIds->addFor($applicationId, $streamId2);

        $this->assertCount(2, $this->inFlightMessageStreamIds->for($applicationId));

        $this->inFlightMessageStreamIds->removeFor($applicationId, $streamId1);

        $inFlightStreamIds = $this->inFlightMessageStreamIds->for($applicationId);

        $this->assertCount(1, $inFlightStreamIds);
        $this->assertNotContains($streamId1->toString(), $inFlightStreamIds);
        $this->assertContains($streamId2->toString(), $inFlightStreamIds);
    }

    #[Test]
    public function itDoesNothingWhenRemovingNonExistentStreamId(): void
    {
        $applicationId = Id::applicationId();
        $streamId1 = Id::streamId();
        $streamId2 = Id::streamId2();

        $this->inFlightMessageStreamIds->addFor($applicationId, $streamId1);

        $this->assertCount(1, $this->inFlightMessageStreamIds->for($applicationId));

        $this->inFlightMessageStreamIds->removeFor($applicationId, $streamId2);

        $inFlightStreamIds = $this->inFlightMessageStreamIds->for($applicationId);

        $this->assertCount(1, $inFlightStreamIds);
        $this->assertContains($streamId1->toString(), $inFlightStreamIds);
    }

    #[Test]
    public function itMaintainsSeparateInFlightListsForDifferentApplications(): void
    {
        $applicationId1 = Id::applicationId();
        $applicationId2 = ApplicationId::fromString('different-app-id');
        $streamId1 = Id::streamId();
        $streamId2 = Id::streamId2();

        $this->inFlightMessageStreamIds->addFor($applicationId1, $streamId1);
        $this->inFlightMessageStreamIds->addFor($applicationId2, $streamId2);

        $inFlightStreamIds1 = $this->inFlightMessageStreamIds->for($applicationId1);
        $inFlightStreamIds2 = $this->inFlightMessageStreamIds->for($applicationId2);

        $this->assertCount(1, $inFlightStreamIds1);
        $this->assertCount(1, $inFlightStreamIds2);
        $this->assertContains($streamId1->toString(), $inFlightStreamIds1);
        $this->assertContains($streamId2->toString(), $inFlightStreamIds2);
        $this->assertNotContains($streamId2->toString(), $inFlightStreamIds1);
        $this->assertNotContains($streamId1->toString(), $inFlightStreamIds2);
    }

    #[Test]
    public function itCanClearAllInFlightMessagesForApplication(): void
    {
        $applicationId = Id::applicationId();
        $streamId1 = Id::streamId();
        $streamId2 = Id::streamId2();

        $this->inFlightMessageStreamIds->addFor($applicationId, $streamId1);
        $this->inFlightMessageStreamIds->addFor($applicationId, $streamId2);

        $this->assertCount(2, $this->inFlightMessageStreamIds->for($applicationId));

        // Remove all stream IDs
        $this->inFlightMessageStreamIds->removeFor($applicationId, $streamId1);
        $this->inFlightMessageStreamIds->removeFor($applicationId, $streamId2);

        $inFlightStreamIds = $this->inFlightMessageStreamIds->for($applicationId);

        $this->assertEmpty($inFlightStreamIds);
    }
}
