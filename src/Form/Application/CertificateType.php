<?php

namespace App\Form\Application;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class CertificateType extends AbstractType
{
    public function getParent(): string
    {
        return TextareaType::class;
    }
}