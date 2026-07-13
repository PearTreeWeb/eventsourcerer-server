<?php

namespace App\Domain\Stream\Model;

use App\Domain\Common\Model\FulfilIsCollection;
use App\Domain\Common\Model\IsCollection;

/**
 * @implements IsCollection<string, StreamEventPayloadProperty>
 */
final class StreamEventPayloadProperties implements IsCollection
{
    /**
     * @use FulfilIsCollection<string, StreamEventPayloadProperty>
     */
    use FulfilIsCollection;
}