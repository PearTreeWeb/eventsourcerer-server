<?php

namespace App\Controller\User;

use App\Domain\User\Command\DeleteUser;
use App\Domain\User\Model\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user/{id}/delete', name: 'delete_user', methods: ['DELETE'])]
#[IsGranted('ROLE_SUPER_USER')]
final class Delete extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'user_deleted_successfully';
    private const string ERROR_MSG_TRANSLATION_KEY   = 'user_deletion_failed';

    public function __construct(private readonly MessageBusInterface $commandBus)
    {

    }

    public function __invoke(UserId $id): Response
    {
        try {
            $this->commandBus->dispatch(
                new DeleteUser($id),
            );

            $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);
        } catch (\Throwable $e) {
            $this->addFlash('warning', self::ERROR_MSG_TRANSLATION_KEY);
        }

        return $this->redirectToRoute('users');
    }
}
