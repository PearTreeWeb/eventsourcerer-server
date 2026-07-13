<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Stream;

use App\Form\Stream\CheckpointType;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<?Checkpoint, int>
 */
final readonly class CheckpointTransformer implements DataTransformerInterface
{
    /**
     * @param Checkpoint $value
     */
    public function transform(mixed $value): int
    {
        return $value->toInt();
    }

    /**
     * @param int $value
     */
    public function reverseTransform(mixed $value): Checkpoint
    {
        return Checkpoint::fromInt($value);
    }
}
