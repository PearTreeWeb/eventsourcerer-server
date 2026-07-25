<?php

declare(strict_types=1);

namespace App\Controller\Projection;

use App\Domain\Common\Model\PropertyType;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventProperties;
use App\Domain\Event\Model\EventProperty;
use App\Domain\Event\Query\GetAllEvents;
use App\Domain\Event\Query\GetEvent;
use App\Domain\Event\Query\GetEventRepresentingAll;
use App\Domain\Projection\Command\SetProjectionMutations;
use App\Domain\Projection\Model\CanMutate;
use App\Domain\Projection\Model\MutationDisplayPart;
use App\Domain\Projection\Model\ProjectionEventProperty;
use App\Domain\Projection\Model\ProjectionEventPropertyId;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Query\GetProjection;
use App\Domain\Projection\Query\GetProjectionPropertyMutations;
use App\Entity\Event;
use App\Entity\MutationConditionsGroup;
use App\Entity\Projection;
use App\Entity\ProjectionMutation;
use App\Entity\ProjectionMutation as ProjectionMutationEntity;
use App\Entity\User;
use App\Form\Projection\DefineStateMutationsType;
use App\Form\Projection\SelectEventType;
use App\Extension\Default\Mutation\Ignore;
use App\Infrastructure\SymfonyQueryBus;
use App\Repository\Mutations;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    '/dashboard/projection/{id}/mutations/{projectionEventPropertyId}/define/{eventId}',
    name: 'define_projection_mutations',
    requirements: ['id' => Requirement::UUID],
    defaults: ['eventId' => '00000000-0000-0000-0000-000000000000']
)]
#[IsGranted('ROLE_USER')]
final class DefineStateMutations extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'projection_updated_successfully';

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly SymfonyQueryBus $queryBus,
        private readonly Mutations $mutations
    ) {}

    public function __invoke(
        Request $request,
        ProjectionId $id,
        ProjectionEventPropertyId $projectionEventPropertyId,
        #[CurrentUser] User $user,
        EventId $eventId
    ): Response {
        $allMutationTypes = $this->mutations->all();

        $projection = $this->queryBus->query(GetProjection::withId($id));
        $event = $eventId->isSet() ? $this->queryBus->query(GetEvent::withId($eventId)) : null;

        /** @var ProjectionEventProperty $property */
        $property = $projection->eventProperties()->get($projectionEventPropertyId);

        $selectEventForm = $this->createFormBuilder(['event' => $event])
            ->add(
                'event',
                SelectEventType::class,
                [
                    'choice_label' => self::eventChoiceLabel(),
                    'choices'      => $this->eventOptions(),
                    'placeholder'  => '-- select --',
                ]
            )->getForm();

        $selectEventForm->handleRequest($request);

        if ($eventId->isSet() || ($selectEventForm->isSubmitted() && $selectEventForm->isValid())) {
            /** @var Event $selectedEvent */
            $selectedEvent = $eventId->isSet()
                ? $this->fetchEvent($eventId)
                : $selectEventForm->get('event')->getData();

            $mutations = $this->queryBus->query(
                new GetProjectionPropertyMutations($selectedEvent->getId(), $projectionEventPropertyId)
            );

            if ($eventId->isNull()) {
                $eventId = $selectedEvent->getId();
            }

            $applicableProperties = self::applicableProperties(
                $projection,
                $selectedEvent->eventProperties(),
                $projectionEventPropertyId,
                self::keyMutationsByEventPropertyId($mutations),
                $this->mutations->all()
            );

            $formData = [
                'properties'    => $applicableProperties,
                'selectedEvent' => $selectedEvent,
            ];

            $form = $this->createForm(
                DefineStateMutationsType::class,
                $formData,
                [
                    'propertyType' => $property->type,
                    'eventProperties' => $selectedEvent
                        ->eventProperties()
                        ->items()
                        ->map(static fn (EventProperty $eventProperty): array => $eventProperty->toArray())
                        ->values()
                        ->all(),
                    'projectionProperties' => $projection
                        ->eventProperties()
                        ->items()
                        ->mapWithKeys(static fn (ProjectionEventProperty $p) => [$p->name->toString() => $p->name->toString()])
                        ->all(),
                ]
            );

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->commandBus->dispatch(
                    new SetProjectionMutations(
                        $id,
                        $eventId,
                        $projectionEventPropertyId,
                        $form->getData()
                    )
                );

                $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);

                return $this->redirectToRoute(
                    'define_projection_mutations',
                    [
                        'id'                        => $id,
                        'eventId'                   => $eventId,
                        'projectionEventPropertyId' => $projectionEventPropertyId,
                    ]
                );
            }
        }

        $mutationTypes = json_encode(
            self::mutationTypes($allMutationTypes),
            JSON_THROW_ON_ERROR
        );

        return $this->render(
            'UI/projection/define_mutations.html.twig',
            [
                'id'                        => $id->toString(),
                'eventId'                   => $eventId,
                'form'                      => $form ?? null,
                'mutationTypes'             => $mutationTypes,
                'property'                  => $property->name->toString(),
                'projection'                => $projection,
                'projectionEventPropertyId' => $projectionEventPropertyId->toString(),
                'projectionTypes'           => $allMutationTypes,
                'propertyType'              => $property->type::name()->toString(),
                'selectEventForm'           => $selectEventForm,
            ]
        );
    }

    /**
     * @param iterable<CanMutate>       $mutations
     * @param array<ProjectionMutation> $definedMutationTypes
     *
     * @return array<string, array{
     *     conditionGroups: array<int, MutationConditionsGroup>,
     *     id: string,
     *     name: string,
     *     mutationType: ?CanMutate,
     *     type: PropertyType,
     * }>
     */
    private static function applicableProperties(
        Projection $state,
        EventProperties $eventProperties,
        ProjectionEventPropertyId $stateEventPropertyId,
        array $definedMutationTypes,
        iterable $mutations
    ): array {
        /** @var PropertyType $filterType */
        $filterType = $state->eventProperties()->get($stateEventPropertyId)->type;
        $mutations  = \iterator_to_array($mutations);

        return $eventProperties
            ->items()
            ->filter(static fn (EventProperty $property): bool => $filterType->canBeUsedWithProjectionPropertyType($property->type))
            ->map(static function (EventProperty $property) use ($definedMutationTypes, $mutations) {
                $mutationType = Ignore::create()::class;
                $conditionGroups = [];

                if (isset($definedMutationTypes[$property->id->toString()])) {
                    $mutationType = $definedMutationTypes[$property->id->toString()]->getType();
                    $conditionGroups = $definedMutationTypes[$property->id->toString()]->getConditionGroups()->toArray();
                }

                return [
                    'containsPersonalData' => false,
                    'conditionGroups' => $conditionGroups,
                    'id' => $property->id->toString(),
                    'name' => $property->name->toString(),
                    'mutationType' => self::mutation($mutations, $mutationType),
                    'type' => $property->type,
                ];
            })->all();
    }

    /**
     * @param iterable<CanMutate> $mutations
     *
     * @return CanMutate|null
     */
    private static function mutation(iterable $mutations, string $type): ?CanMutate
    {
        foreach ($mutations as $mutation) {
            if ($mutation->uniqueIdentifier()->toString() === $type) {
                return $mutation;
            }
        }

        return null;
    }

    /**
     * @param array<ProjectionMutationEntity> $mutations
     *
     * @return array<string, ProjectionMutationEntity>
     */
    private static function keyMutationsByEventPropertyId(array $mutations): array
    {
        return collect($mutations)
            ->keyBy(static fn (ProjectionMutationEntity $entity) => $entity->getEventPropertyId()->toString())
            ->all();
    }

    private static function eventChoiceLabel(): callable
    {
        return static function (Event $event): string {
            if ($event->getId()->isAny()) {
                return 'any event occurs';
            }

            return sprintf('%s (v%d)', $event->getName(), $event->getVersion());
        };
    }

    /**
     * @param iterable<CanMutate> $projectionTypes
     *
     * @return CanMutate[]
     */
    private static function mutationTypes(iterable $projectionTypes): array
    {
        return collect($projectionTypes)
            ->mapWithKeys(self::mapDisplayParts())
            ->all();
    }

    private static function mapDisplayParts(): callable
    {
        return static function (CanMutate $projectionType) {
            return [
                $projectionType::label()->toString() => \array_map(
                    static fn (MutationDisplayPart $part): string => $part->value,
                    $projectionType::displayOrder()
                )
            ];
        };
    }

    /**
     * @return Event[]
     */
    private function eventOptions(): array
    {
        return \array_merge(
            $this->queryBus->query(new GetAllEvents()),
            [
                $this->queryBus->query(new GetEventRepresentingAll()),
            ]
        );
    }

    private function fetchEvent(EventId $eventId): Event
    {
        return $this->queryBus->query(GetEvent::withId($eventId));
    }
}
