<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Domain\User\Command\UpdateUser;
use App\Domain\User\Model\Role;
use App\Domain\User\Model\UserId;
use App\Domain\User\Query\GetUser;
use App\Form\User\EditUserType;
use App\Infrastructure\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user/{id}/edit', name: 'edit_user')]
#[IsGranted('ROLE_SUPER_USER')]
final class Edit extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'user_updated_successfully';

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly QueryBus $queryBus
    ) {}

    public function __invoke(Request $request, UserId $id): Response
    {
        $isSuperUser = $this->isGranted('ROLE_SUPER_USER');
        $user = $this->queryBus->query(GetUser::withId($id));
        $form = $this->createForm(EditUserType::class, $user, [
            'is_super_user' => $isSuperUser,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$isSuperUser && Role::from($user->getRoles()[0]) !== $form->getData()->role) {
                $this->addFlash('error', 'You do not have permission to change the role');

                return $this->redirectToRoute('edit_user', ['id' => $id->toString()]);
            }

            $this->commandBus->dispatch(new UpdateUser($form->getData()));

            $this->addFlash(
                'success',
                self::SUCCESS_MSG_TRANSLATION_KEY
            );

            return $this->redirectToRoute('users');
        }

        return $this->render('UI/user/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
