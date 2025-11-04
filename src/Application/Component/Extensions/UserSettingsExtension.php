<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
declare(strict_types=1);

namespace Slcorp\AdminBundle\Application\Component\Extensions;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Slcorp\AdminBundle\Application\Component\BaseAdmin;
use Slcorp\AdminBundle\Application\Service\CapabilityServiceInterface;
use Slcorp\AdminBundle\Application\Service\TableUserPreferenceService;
use Sonata\AdminBundle\Admin\AdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @phpstan-template T of object
 * @template-implements AdminExtensionInterface<T>
 * @phpstan-method void preBatchAction(AdminInterface<T> $admin, string $actionName, ProxyQueryInterface<T> $query, array &$idx, bool $allElements)
 */
class UserSettingsExtension implements AdminExtensionInterface
{
    /** @var BaseAdmin<T> $admin */
    private BaseAdmin $admin;

    public function __construct(
        protected TranslatorInterface        $translator,
        protected CapabilityServiceInterface $capabilityService,
        protected TableUserPreferenceService $userPreferenceService,
    ) {
    }

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configure(AdminInterface $admin): void
    {
        if (!$admin instanceof BaseAdmin) {
            throw new \RuntimeException('Incorrect admin class');
        }
        $this->admin = $admin;
    }

    public function configureFormFields(FormMapper $form): void
    {
    }

    public function configureListFields(ListMapper $list): void
    {
        $userPreferences = $this->admin->userPreferences();
        $order = $this->userPreferenceService->tableOrder($userPreferences);
        $hidden = $this->userPreferenceService->tableHidden($userPreferences);

        // При первичной инициализации записываем настройки пользователей - все поля по умолчанию включены
        if (empty($order) && empty($hidden)) {
            $tableData = $userPreferences->getTableData();
            $tableData['order'] = array_values(array_filter($list->keys(), static fn($key) => !in_array($list->get($key)->getType(), ['actions', 'batch'])));
            $this->userPreferenceService->setTableData($userPreferences->getTableClass(), $tableData);
        }
    }

    public function configureDatagridFilters(DatagridMapper $filter): void
    {
    }

    public function configureShowFields(ShowMapper $show): void
    {
    }

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        /**Настройки таблицы, получение и запись*/
        $collection->add('getSettings');
        $collection->add('setSettings');
        $collection->add('orderChanged');
    }

    public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
    }

    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query): void
    {
    }

    public function alterNewInstance(AdminInterface $admin, object $object): void
    {
    }

    public function alterObject(AdminInterface $admin, object $object): void
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function configurePersistentParameters(AdminInterface $admin, array $parameters)
    {
        return $parameters;
    }

    public function getAccessMapping(AdminInterface $admin): array
    {
        return [];
    }

    public function configureBatchActions(AdminInterface $admin, array $actions): array
    {
        return $actions;
    }

    public function configureExportFields(AdminInterface $admin, array $fields): array
    {
        return $fields;
    }

    public function preUpdate(AdminInterface $admin, object $object): void
    {
    }

    public function postUpdate(AdminInterface $admin, object $object): void
    {
    }

    public function prePersist(AdminInterface $admin, object $object): void
    {
    }

    public function postPersist(AdminInterface $admin, object $object): void
    {
    }

    public function preRemove(AdminInterface $admin, object $object): void
    {
    }

    public function postRemove(AdminInterface $admin, object $object): void
    {
    }

    public function configureActionButtons(AdminInterface $admin, array $list, string $action, ?object $object = null): array
    {
        return $list;
    }

    public function configureDashboardActions(AdminInterface $admin, array $actions): array
    {
        return $actions;
    }

    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void
    {
    }

    public function configureDefaultSortValues(AdminInterface $admin, array &$sortValues): void
    {
    }

    public function configureFormOptions(AdminInterface $admin, array &$formOptions): void
    {
    }

    public function configureFilterParameters(AdminInterface $admin, array $parameters): array
    {
        return $parameters;
    }

    public function __call(string $name, array $arguments): void
    {
    }
}
