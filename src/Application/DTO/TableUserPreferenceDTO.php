<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle\Application\DTO;

use Slcorp\RoleModelBundle\Domain\Entity\User;

class TableUserPreferenceDTO
{
    public string $className;

    public User $user;

    public array $order = [];
    public array $hidden = [];

    public function toArray(): array
    {
        return [
            'className' => $this->className,
            'user_id' => $this->user->getId(),
            'order' => $this->order,
            'hidden' => $this->hidden,
        ];
    }
}
