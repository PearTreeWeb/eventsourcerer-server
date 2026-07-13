<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\User\Exception\NoUserExists;
use App\Domain\User\Model\EmailAddress;
use App\Domain\User\Model\Role;
use App\Domain\User\Model\UserId;
use App\Domain\User\Repository\UserRepository as UserRepositoryInterface;
use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

final readonly class DoctrineUserRepository implements UserRepositoryInterface
{
    /**
     * @var EntityRepository<User>
     */
    private EntityRepository $repository;

    /**
     * @var EntityRepository<ResetPasswordRequest>
     */
    private EntityRepository $resetPasswordRepository;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->repository = $this->entityManager->getRepository(User::class);
        $this->resetPasswordRepository = $this->entityManager->getRepository(ResetPasswordRequest::class);
    }

    public function create(User $user, \DateTimeImmutable $createdAt): User
    {
        $user
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($createdAt);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function update(User $user, \DateTimeImmutable $updatedAt): User
    {
        $user->setUpdatedAt($updatedAt);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function all(): array
    {
        return $this->repository->findAll();
    }

    public function withIdStrict(UserId $id): User
    {
        return $this->repository->find($id->toUuid())
            ?? throw NoUserExists::withId($id->toString());
    }

    public function hasOnlyOneSuperUser(): bool
    {
        $sql = <<<SQL
            SELECT id
            FROM "user"
            WHERE roles::jsonb ?? :role 
        SQL;

        $connection = $this->entityManager->getConnection();
        $statement  = $connection->prepare($sql);

        $statement->bindValue('role', Role::SUPER_USER->value);

        return 1 === $statement->executeQuery()->rowCount();
    }

    public function withEmailAddressStrict(EmailAddress $emailAddress): User
    {
        return $this->repository->findOneBy([
            'email' => $emailAddress->toString(),
        ]) ?? throw NoUserExists::withEmailAddress($emailAddress);
    }

    public function delete(UserId $userId): void
    {
        $user = $this->withIdStrict($userId);

        $resetPasswordEntries = $this->resetPasswordRepository->findBy([
            'user' => $user,
        ]);

        foreach ($resetPasswordEntries as $resetPasswordEntry) {
            $this->entityManager->remove($resetPasswordEntry);
        }

        $this->entityManager->remove($this->withIdStrict($userId));
        $this->entityManager->flush();
    }
}
