<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle\Application\Service;

use Slcorp\AdminBundle\Application\DTO\TableUserPreferenceDTO;
use Slcorp\AdminBundle\Domain\Entity\TableUserPreference;
use Slcorp\AdminBundle\Domain\Repository\TableUserPreferenceRepositoryInterface;
use Slcorp\RoleModelBundle\Domain\Entity\User;
use Slcorp\RoleModelBundle\Domain\Repository\UserRepositoryInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TableUserPreferenceService
{
    public function __construct(
        private readonly TableUserPreferenceRepositoryInterface $repository,
        private readonly Security $security,
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function allUserPreferences(): array
    {
        return $this->repository->findAllByUser($this->security->getUser());
    }

    public function buildDTO(array $array = []): TableUserPreferenceDTO
    {
        $dto = new TableUserPreferenceDTO();

        foreach ($array as $key => $value) {
            if (property_exists($dto, $key)) {
                if ('user' === $key || 'user_id' === $key) {
                    if ($value instanceof User) {
                        $dto->user = $value;
                    } else {
                        $dto->user = $this->userRepository->find($value);
                    }
                } else {
                    $dto->$key = $value;
                }
            }
        }

        return $dto;
    }

    public function userPreferencesByTable(string $table): TableUserPreference
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \RuntimeException('User not found for preference ' . $table);
        }
        if (!$user instanceof User) {
            throw new \RuntimeException('Incorrect user');
        }
        $pref = $this->repository->findUserPreferenceByTable($user, $table);

        if (!$pref) {
            $prefObject = new TableUserPreference();
            $prefObject->setUser($user);
            $prefObject->setTableClass($table);
            $prefObject->setTableData($this->buildDTO(['className' => $table, 'user' => $user])->toArray());
            $pref = $this->repository->save($prefObject);
        }

        return $pref;
    }

    public function tablePreferenceDTO(TableUserPreference $pref): TableUserPreferenceDTO
    {
        return $this->buildDTO(
            $pref->getTableData()
            + [
                'className' => $pref->getTableClass(),
                'user' => $pref->getUser(),
            ]
        );
    }

    public function tableOrder(TableUserPreference $pref): array
    {
        return $this->tablePreferenceDTO($pref)->order;
    }

    public function tableHidden(TableUserPreference $pref): array
    {
        return $this->tablePreferenceDTO($pref)->hidden;
    }

    public function getTableData(string $table): array
    {
        $instance = $this->userPreferencesByTable($table);

        return $this->tablePreferenceDTO($instance)->toArray();
    }

    public function setTableData(string $table, array $tableData): void
    {
        $preference = $this->userPreferencesByTable($table);
        $dto = $this->buildDTO(
            $tableData
            + [
                'className' => $preference->getTableClass(),
                'user' => $preference->getUser(),
            ]
        );
        $preference->setTableData($dto->toArray());
        $this->repository->save($preference);
    }
}
