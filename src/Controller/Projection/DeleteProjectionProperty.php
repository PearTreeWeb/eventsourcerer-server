<?php

declare(strict_types=1);

namespace App\Controller\Projection;

use App\Domain\Projection\Model\ProjectionPropertyId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    '/dashboard/projection/property/{id}/delete',
    name: 'delete_projection_property',
    requirements: ['id' => Requirement::UUID],
    methods: ['GET']
)]
#[IsGranted('ROLE_USER')]
final class DeleteProjectionProperty extends AbstractController
{
    public function __invoke(ProjectionPropertyId $id): Response
    {
        // @todo complete me!!!
        return new Response('Complete me!');
    }
}
