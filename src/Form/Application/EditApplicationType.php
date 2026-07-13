<?php

declare(strict_types=1);

namespace App\Form\Application;

use App\Domain\Application\Command\EditApplication;
use App\Domain\Application\Model\ApplicationName;
use App\Entity\Application;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;

final class EditApplicationType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', ApplicationIdType::class)
            ->add('name', ApplicationNameType::class)
            ->add('hostname')
            ->setDataMapper($this);
    }

    /**
     * @param Application $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $forms = \iterator_to_array($forms);

        $forms['id']->setData(ApplicationId::fromString($viewData->id()->toString()));
        $forms['name']->setData(ApplicationName::fromString($viewData->name()));
        $forms['hostname']->setData($viewData->hostname());
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        $viewData = new EditApplication(
            $forms['id']->getData(),
            $forms['name']->getData(),
            $forms['hostname']->getData(),
        );
    }
}
