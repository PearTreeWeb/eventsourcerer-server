<?php

namespace App\Controller\Tool;

use App\Domain\Settings\Query\GetSettings;
use App\Domain\Tool\Command\EncryptPersonalData;
use App\Form\Tool\EncryptPersonalDataFormType;
use App\Infrastructure\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/tools/all', name: 'tools')]
final class All extends AbstractController
{
    private const string ERROR_MSG_TRANSLATION_KEY = 'personal_data_not_encrypted';
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'personal_data_encrypted';

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(EncryptPersonalDataFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{beforeDate: \DateTimeImmutable} $formData */
            $formData = $form->getData();

            try {
                $this->commandBus->dispatch(new EncryptPersonalData($formData['beforeDate']));

                $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);
            } catch (\Throwable) {
                $this->addFlash('warning', self::ERROR_MSG_TRANSLATION_KEY);
            }

            return $this->redirectToRoute('tools');
        }

        return $this->render('UI/tool/all.html.twig', [
            'form' => $form,
            'settings' => $this->queryBus->query(new GetSettings()),
        ]);
    }
}
