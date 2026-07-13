<?php

namespace App\Form\Settings;

use App\Domain\Settings\Model\PublicSshKey;
use App\Entity\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PublicSshKeyType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('key', TextareaType::class, [
                'constraints' => [
                    new Callback([$this, 'validateSshKey']),
                ],
            ])
            ->setDataMapper($this);
        ;
    }

    public function validateSshKey(?string $value, ExecutionContextInterface $context): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (str_starts_with($value, '-----BEGIN PUBLIC KEY-----')) {
            return;
        }

        $parts = explode(' ', trim($value));
        $type = $parts[0];

        if ($type !== 'ssh-rsa') {
            $context->buildViolation('Unsupported SSH key type: {{ type }}. Only ssh-rsa is supported for personal data encryption.')
                ->setParameter('{{ type }}', $type)
                ->addViolation();
        }
    }

    /**
     * @param ?PublicSshKey $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $forms = iterator_to_array($forms);

        $forms['key']->setData($viewData);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        $viewData = PublicSshKey::fromString($forms['key']->getData());
    }
}