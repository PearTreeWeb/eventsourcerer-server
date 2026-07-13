<?php

declare(strict_types=1);

namespace App\Form\Application;

use App\Domain\Application\Command\RegisterApplication;
use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Event\Model\EventName;
use App\Entity\Event;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;

final class RegisterApplicationType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly GenerateUuid $generateUuid) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', ApplicationNameType::class)
            ->add('hostname', HostnameType::class)
            ->setDataMapper($this);
    }

    /**
     * @param ?Event $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        if (!$viewData) {
            return;
        }

        $forms = iterator_to_array($forms);

        $forms['name']->setData(EventName::fromString($viewData->getName()));
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        $applicationName = $forms['name']->getData();

        $viewData = new RegisterApplication(
            ApplicationId::fromString($this->generateUuid->for($applicationName)->toString()),
            $applicationName,
            $forms['hostname']->getData(),
        );
    }
}
