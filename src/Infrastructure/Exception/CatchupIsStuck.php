<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception;

use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

final class CatchupIsStuck extends \RuntimeException
{
    public static function waitingForMessageAcknowledgementFor(
        WorkerId $workerId,
        StreamId $streamId,
        Checkpoint $checkpoint
    ): self {
        return new self(
            sprintf(
                'Catchup process is stuck. Worker "%s" has not acknowledged event %d for stream %s',
                $workerId,
                $checkpoint->toInt(),
                $streamId
            )
        );
    }
}
