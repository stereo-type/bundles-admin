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
use Slcorp\RoleModelBundle\Application\Enum\CapabilityAction;
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
 *
 * @template-implements AdminExtensionInterface<T>
 *
 * @phpstan-method void preBatchAction(AdminInterface<T> $admin, string $actionName, ProxyQueryInterface<T> $query, array &$idx, bool $allElements)
 */
class AdminControlExtension implements AdminExtensionInterface
{
    /** @var BaseAdmin<T> */
    private BaseAdmin $admin;

    public function __construct(
        protected TranslatorInterface $translator,
        protected CapabilityServiceInterface $capabilityService,
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

    public function configureListFields(ListMapper $list): void
    {
        $actionNames = [
            CapabilityAction::EDIT,
            CapabilityAction::DELETE,
        ];

        $actions = $this->configureTableActionButtons($actionNames);

        if (!empty($actions)) {
            $list->add($this->translator->trans('action'), 'actions', [
                'actions' => $actions,
            ]);
        }
    }

    /**Метод для формирования кнопок управления строкой в таблице
     * @param array $actionNames
     * @return array
     */
    private function configureTableActionButtons(array $actionNames): array
    {
        $actions = [];
        foreach ($actionNames as $actionName) {
            $action = $actionName->value;
            if ($this->capabilityService->isGranted($this->admin, $action, $this)) {
                $actions[$action] = [
                    'label' => $this->translator->trans($action),
                ];
            }
        }

        return $actions;
    }

    public function configureBatchActions(AdminInterface $admin, array $actions): array
    {
        unset($actions['delete']);

        return $actions;
    }

    public function configureExportFields(AdminInterface $admin, array $fields): array
    {
        return $fields;
    }

    /**
     * Метод для формирования кнопок действий - над правой частью таблицы.
     */
    public function configureActionButtons(AdminInterface $admin, array $list, string $action, ?object $object = null): array
    {
        /**Добавляем кнопку только на список - кнопка-просмотр дерева операций*/
        if ('list' === $action) {
            $list['settings'] = [
                'template' => '@SlcorpAdmin/Button/modal_button.twig',
                'parameters' => [
                    'label' => 'Настройки',
                    'icon' => 'fa fa-gears',
                    'data_getter' => $this->admin->generateUrl('getSettings'),
                    'data_action' => $this->admin->generateUrl('setSettings'),
                    'table_name' => $admin::class,
                ],
            ];
        }

        return $list;
    }

    public function getAccessMapping(AdminInterface $admin): array
    {
        return [];
    }

    public function configureFormFields(FormMapper $form): void
    {
    }

    public function configureDatagridFilters(DatagridMapper $filter): void
    {
    }

    public function configureShowFields(ShowMapper $show): void
    {
    }

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
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

    public function configureDashboardActions(AdminInterface $admin, array $actions): array
    {
        return $actions;
    }

    public function configureFilterParameters(AdminInterface $admin, array $parameters): array
    {
        return $parameters;
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

    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void
    {
    }

    public function configureDefaultSortValues(AdminInterface $admin, array &$sortValues): void
    {
    }

    public function configureFormOptions(AdminInterface $admin, array &$formOptions): void
    {
    }

    public function __call(string $name, array $arguments): void
    {
    }
}
