<?php

declare(strict_types=1);

namespace App\Controller\Application;

use App\Domain\Application\Command\EditApplication;
use App\Domain\Application\Query\GetApplication;
use App\Domain\Application\Service\CertificateGenerator;
use App\Form\Application\EditApplicationType;
use App\Form\Application\ReplaceApplicationCertificateKeyType;
use App\Form\Application\ReplaceApplicationCertificateType;
use App\Infrastructure\QueryBus;
use League\Flysystem\FilesystemOperator;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/application/{id}/edit', name: 'edit_application')]
#[IsGranted('ROLE_SUPER_USER')]
final class Edit extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'application_updated_successfully';
    private const string CERTIFICATE_UPDATED_SUCCESSFULLY_MESSAGE = 'application_certificate_successfully_updated';
    private const string CERTIFICATE_UPDATE_FAILED_MESSAGE = 'application_certificate_update_failed';

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly MessageBusInterface $commandBus,
        private readonly FilesystemOperator $appCertsClient,
        private readonly CertificateGenerator $certificateGenerator,
    ) {}

    public function __invoke(ApplicationId $id, Request $request): Response
    {
        $application = $this->queryBus->query(new GetApplication($id));
        $form = $this->createForm(EditApplicationType::class, $application);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var EditApplication $command */
                $command = $form->getData();

                $this->commandBus->dispatch($command);

                $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);

                return $this->redirectToRoute('applications');
            } catch (\Throwable $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }


        $certFilename = $this->certificateGenerator->certFilename($application->hostnameValueObject());
        $certForm = $this->createForm(
            ReplaceApplicationCertificateType::class,
            ['certificate' => $this->appCertsClient->read($certFilename)],
        );

        $certForm->handleRequest($request);

        if ($certForm->isSubmitted() && $certForm->isValid()) {
            try {
                $this->appCertsClient->write($certFilename, $certForm->getData()['certificate']);

                $this->addFlash('success', self::CERTIFICATE_UPDATED_SUCCESSFULLY_MESSAGE);
            } catch (\Throwable) {
                $this->addFlash('warning', self::CERTIFICATE_UPDATE_FAILED_MESSAGE);
            }

            return $this->redirectToRoute('edit_application', ['id' => $id->toString()]);
        }

        $certKeyFilename = $this->certificateGenerator->keyFilename($application->hostnameValueObject());

        $certKeyForm = $this->createForm(
            ReplaceApplicationCertificateKeyType::class,
            ['certificate' => $this->appCertsClient->read($certKeyFilename)],
        );

        $certKeyForm->handleRequest($request);

        if ($certKeyForm->isSubmitted() && $certKeyForm->isValid()) {
            try {
                $this->appCertsClient->write($certKeyFilename, $certKeyForm->getData()['certificate']);

                $this->addFlash('success', self::CERTIFICATE_UPDATED_SUCCESSFULLY_MESSAGE);
            } catch (\Throwable) {
                $this->addFlash('warning', self::CERTIFICATE_UPDATE_FAILED_MESSAGE);
            }

            return $this->redirectToRoute('edit_application', ['id' => $id->toString()]);
        }

        return $this->render(
            'UI/application/edit.html.twig',
            [
                'form' => $form,
                'certForm' => $certForm,
                'certKeyForm' => $certKeyForm,
                'id' => $id->toString(),
                'regenerateSecretForm' => $this->createFormBuilder()
                    ->setAction($this->generateUrl('regenerate_application_secret', ['id' => $id->toString()]))
                    ->setMethod('POST')
                    ->getForm()
                    ->createView(),
            ]
        );
    }
}
