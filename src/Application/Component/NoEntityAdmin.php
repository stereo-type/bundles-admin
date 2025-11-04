<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle\Application\Component;

use Doctrine\ORM\EntityManagerInterface;
use Slcorp\AdminBundle\Application\Component\Factories\NotEntityFieldDescriptionFactory;
use Slcorp\AdminBundle\Application\Service\CapabilityServiceInterface;
use Slcorp\AdminBundle\Application\Service\TableUserPreferenceService;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Упрощенный вариант BaseAdmin для вывода сущностей не требующих CUD.
 *
 * @phpstan-template T of object
 *
 * @phpstan-extends SimpleAdmin<T>
 */
abstract class NoEntityAdmin extends SimpleAdmin
{
    protected function configure(): void
    {
        parent::configure();
        /**Меняем страндартную фабрику создания описания полей, которая завязана по умолчанию на Доктрину */
        $this->setFieldDescriptionFactory(new NotEntityFieldDescriptionFactory());
    }

    protected function configureListFields(ListMapper $list): void
    {
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
    }
}
