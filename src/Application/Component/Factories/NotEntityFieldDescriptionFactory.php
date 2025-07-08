<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace AcademCity\AdminBundle\Application\Component\Factories;

use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\DoctrineORMAdminBundle\FieldDescription\FieldDescription;

class NotEntityFieldDescriptionFactory implements FieldDescriptionFactoryInterface
{
    public function __construct()
    {
    }

    public function create(string $class, string $name, array $options = []): FieldDescriptionInterface
    {
        // Здесь создаем только FieldDescription с нужными параметрами
        return new FieldDescription(
            $name,
            $options,
            [], // Пустая ассоциация и маппинг, если не нужно
            [],
            [],
            $name
        );
    }
}
