<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Stream;

use App\Form\DataTransformer\StringTransformer;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<?StreamId, string>
 */
final class StreamIdTransformer extends StringTransformer implements DataTransformerInterface
{
    /**
     * @param string $value
     */
    public function reverseTransform(mixed $value): StreamId
    {
        return StreamId::fromString($value);
    }
}
