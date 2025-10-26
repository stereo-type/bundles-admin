<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle\Application\Component;

/**
 * Упрощенный вариант BaseAdmin для вывода сущностей не требующих CUD.
 *
 * @phpstan-template T of object
 *
 * @phpstan-extends BaseAdmin<T>
 */
abstract class SimpleAdmin extends BaseAdmin
{
    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $buttonList = parent::configureActionButtons($buttonList, $action, $object);
        unset($buttonList['create']);

        return $buttonList;
    }

    protected function configureTableActionButtons(array $actionNames): array
    {
        return [];
    }

    protected function configureDashboardActions(array $actions): array
    {
        unset($actions['create']);

        return $actions;
    }
}
