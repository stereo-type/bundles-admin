<?php

namespace Slcorp\AdminBundle\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Slcorp\RoleModelBundle\Domain\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: 'admin_bundle_table_user_preference')]
#[ORM\UniqueConstraint(name: 'user_table_class_unique', columns: ['user_id', 'table_class'])]
class TableUserPreference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(name: 'table_class', length: 255, nullable: false)]
    private string $tableClass;

    #[ORM\Column(name: 'table_data')]
    private array $tableData = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTableClass(): string
    {
        return $this->tableClass;
    }

    public function setTableClass(string $tableClass): static
    {
        $this->tableClass = $tableClass;

        return $this;
    }

    public function getTableData(): array
    {
        return $this->tableData;
    }

    public function setTableData(array $tableData): static
    {
        $this->tableData = $tableData;

        return $this;
    }
}
