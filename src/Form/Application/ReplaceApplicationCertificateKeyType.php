<?php

namespace App\Form\Application;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class ReplaceApplicationCertificateKeyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       $builder->add('certificate', CertificateType::class);
    }

    public function getBlockPrefix(): string
    {
        return 'replace_application_certificate_key';
    }
}
