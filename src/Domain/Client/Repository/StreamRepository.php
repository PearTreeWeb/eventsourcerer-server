<?php

declare(strict_types=1);

namespace App\Domain\Client\Repository;

use App\Entity\Stream;
use App\Entity\StreamEvent;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

interface StreamRepository
{
    public function update(Stream $stream): Stream;

    public function addEvent(Stream $stream, StreamEvent $event): void;

    /**
     * @return Paginator<Stream>
     */
    public function paginated(int $start, int $end, ?string $search = null): \Countable&\IteratorAggregate;

    public function withId(StreamId $id): ?Stream;

    public function withIdStrict(StreamId $id): Stream;

    public function find(StreamId $id): ?Stream;
}
