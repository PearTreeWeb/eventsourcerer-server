<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSourcerer\Service;

use App\Domain\Common\Service\GenerateUuid as GenerateUuidInterface;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;
use Symfony\Component\Uid\Factory\UuidFactory;
use Symfony\Component\Uid\Uuid;

final readonly class GenerateUuid implements GenerateUuidInterface
{
    private const string NAMESPACE = '33dd205d-0fae-4014-825b-b223316cdc1a';

    public function __construct(private UuidFactory $uuidFactory) {}

    public function for(IsString $object): Uuid
    {
        return $this
            ->uuidFactory
            ->nameBased(self::NAMESPACE)
            ->create($object->toString()
        );
    }

    public function random(): Uuid
    {
        return $this->uuidFactory->randomBased()->create();
    }
}
