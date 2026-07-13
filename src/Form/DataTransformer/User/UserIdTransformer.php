<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\User;

use App\Domain\User\Model\UserId;
use App\Form\DataTransformer\StringTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<?UserId, string>
 */
final class UserIdTransformer extends StringTransformer implements DataTransformerInterface
{
    /**
     * @param string $value
     */
    public function reverseTransform(mixed $value): UserId
    {
        return UserId::fromString($value);
    }
}
