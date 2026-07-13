<?php

declare(strict_types=1);

namespace App\Controller\Widget;

use App\Domain\Widget\Model\WidgetName;
use App\Repository\WidgetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/widget/{name}', name: 'load_widget')]
final class LoadWidget extends AbstractController
{
    public function __construct(private readonly WidgetRepository $widgetRepository) {}

    public function __invoke(WidgetName $name): Response
    {
        $widget = $this->widgetRepository->findStrict($name);

        return $this->render('UI/widget/view.html.twig',
            [
                'name' => $name,
                'view' => $widget->view(),
            ]
        );
    }
}
