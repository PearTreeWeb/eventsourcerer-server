<?php

declare(strict_types=1);

namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiDto\Application as ApplicationDto;
use App\Domain\Application\Command\RegisterApplication;
use App\Domain\Application\Model\ApplicationName;
use App\Domain\Common\Service\GenerateUuid;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<ApplicationDto, ApplicationDto>
 */
final class ApplicationProcessor implements ProcessorInterface
{
    use HandleTrait;

    public function __construct(
        MessageBusInterface $messageBus,
        private readonly GenerateUuid $generateUuid,
    ) {
        $this->messageBus = $messageBus;
    }

    /**
     * @param ApplicationDto $data
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): ApplicationDto {
        $applicationId = ApplicationId::fromString($this->generateUuid->random()->toRfc4122());

        $secret = $this->handle(
            new RegisterApplication(
                $applicationId,
                ApplicationName::fromString($data->name),
                $data->hostname,
            )
        );

        $data->id     = $applicationId->toString();
        $data->secret = (string) $secret;

        return $data;
    }
}
