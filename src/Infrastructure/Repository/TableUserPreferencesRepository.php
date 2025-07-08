<?php

namespace AcademCity\AdminBundle\Infrastructure\Repository;

use AcademCity\AdminBundle\Domain\Entity\TableUserPreference;
use AcademCity\AdminBundle\Domain\Repository\TableUserPreferenceRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class TableUserPreferencesRepository implements TableUserPreferenceRepositoryInterface
{
    /**
     * @var EntityRepository<TableUserPreference>
     */
    private EntityRepository $entityRepository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->entityRepository = $this->entityManager->getRepository(TableUserPreference::class);
    }

    public function find(int $id): ?TableUserPreference
    {
        return $this->entityRepository->find($id);
    }

    public function save(TableUserPreference $preference, bool $flush = true): TableUserPreference
    {
        $this->entityManager->persist($preference);
        if ($flush) {
            $this->entityManager->flush();
        }

        return $preference;
    }

    public function delete(TableUserPreference $preference, bool $flush = true): void
    {
        $this->entityManager->remove($preference);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findAllByUser(UserInterface $user): array
    {
        return $this->entityRepository->findBy(['user' => $user]);
    }

    public function findUserPreferenceByTable(UserInterface $user, string $tableClass): ?TableUserPreference
    {
        return $this->entityRepository->findOneBy(['user' => $user, 'tableClass' => $tableClass]);
    }
}
