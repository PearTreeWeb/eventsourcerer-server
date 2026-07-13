<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Settings\Repository\SettingsRepository;
use App\Entity\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineSettingsRepository implements SettingsRepository
{
    /**
     * @var EntityRepository<Settings>
     */
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $this->entityManager->getRepository(Settings::class);
    }

    public function get(): ?Settings
    {
        return $this->repository->find(1);
    }

    public function getOrCreate(\DateTimeImmutable $now): Settings
    {
        return $this->get() ?? Settings::create($now);
    }

    public function update(Settings $settings): Settings
    {
        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        return $settings;
    }
}
