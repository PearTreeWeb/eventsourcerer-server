<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use PearTreeWeb\EventSourcerer\Common\Model\IsString;

abstract class StringTransformer
{
    /**
     * @param null|IsString $value
     *
     * @return string
     */
    public function transform(mixed $value): string
    {
        return $value
            ? $value->toString()
            : IsString::NULL_REPRESENTATION;
    }
}
