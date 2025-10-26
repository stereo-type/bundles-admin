<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle\Domain\Repository;

use Slcorp\AdminBundle\Domain\Entity\TableUserPreference;
use Symfony\Component\Security\Core\User\UserInterface;

interface TableUserPreferenceRepositoryInterface
{
    public function find(int $id): ?TableUserPreference;

    public function save(TableUserPreference $preference, bool $flush = true): TableUserPreference;

    public function delete(TableUserPreference $preference, bool $flush = true): void;

    public function findAllByUser(UserInterface $user): array;

    public function findUserPreferenceByTable(UserInterface $user, string $tableClass): ?TableUserPreference;
}
