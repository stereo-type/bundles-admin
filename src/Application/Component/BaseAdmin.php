<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle\Application\Component;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\AssociationMapping;
use Doctrine\ORM\Mapping\ClassMetadata;
use Slcorp\AdminBundle\Application\Component\Form\FormManyToManyPresentation;
use Slcorp\AdminBundle\Application\Service\CapabilityServiceInterface;
use Slcorp\AdminBundle\Application\Service\TableUserPreferenceService;
use Slcorp\AdminBundle\Domain\Entity\TableUserPreference;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @phpstan-template T of object
 *
 * @phpstan-extends AbstractAdmin<T>
 */
abstract class BaseAdmin extends AbstractAdmin
{
    private array $ignoreTableFieldsList;
    private array $ignoreFormFieldsList;
    private array $ignoreShowFieldsList;
    private array $ignoreFiltersFieldsList;
    private array $fieldListNames;

    private const array TIME_FIELDS = ['deleted_at', 'time_created', 'time_modified', 'created_at', 'updated_at'];
    private const array USER_FIELDS = ['user_created', 'user_modified', 'created_by', 'updated_by', 'modified_by'];

    protected FormManyToManyPresentation $formManyToManyPresentation = FormManyToManyPresentation::CHECK_BOX;

    protected TableUserPreference $userPreference;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TranslatorInterface $translator,
        protected CapabilityServiceInterface $capabilityService,
        protected TableUserPreferenceService $userPreferenceService,
        protected array $extensions,
    ) {
        parent::__construct();
        $this->ignoreTableFieldsList = $this->ignoreTableFieldsList();
        $this->ignoreFormFieldsList = $this->ignoreFormFieldsList();
        $this->ignoreShowFieldsList = $this->ignoreShowFieldsList();
        $this->ignoreFiltersFieldsList = $this->ignoreFiltersFieldsList();
        $this->fieldListNames = $this->fieldListNames();
        $this->setSecurityHandler($capabilityService);
        foreach ($this->extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**Служебный метод не предназначен для переопределения
     * @phpstan-return  ClassMetadata<T>
     */
    final public function entityMetadata(): ClassMetadata // @phpstan-ignore-line
    {
        return $this->entityManager->getClassMetadata($this->getClass());
    }

    /**Служебный метод не предназначен для переопределения
     * @return string[]
     */
    final protected function entityFields(): array
    {
        return $this->entityMetadata()->getFieldNames();
    }

    /**Служебный метод не предназначен для переопределения
     * @return array<string, AssociationMapping>
     *
     */
    final protected function entityAssociations(): array
    {
        return $this->entityMetadata()->getAssociationMappings();
    }

    final protected function entityShortName(): string
    {
        $meta = $this->entityMetadata();

        return $meta->getReflectionClass()->getShortName();
    }

    final public function userPreferences(): TableUserPreference
    {
        if (isset($this->userPreference)) {
            return $this->userPreference;
        }

        $this->userPreference = $this->userPreferenceService->userPreferencesByTable(static::class);

        return $this->userPreference;
    }

    /**Метод генерации массива колонок для таблицы
     * @param ListMapper $list
     * @return void
     */
    protected function configureListFields(ListMapper $list): void
    {
        $ignoreFieldsList = $this->ignoreTableFieldsList;
        $userPreferences = $this->userPreferences();
        $order = $this->userPreferenceService->tableOrder($userPreferences);
        $hidden = $this->userPreferenceService->tableHidden($userPreferences);

        $fields = $this->entityFields();

        // Сортируем поля согласно порядку из order
        $orderedFields = [];
        foreach ($order as $fieldName) {
            if (in_array($fieldName, $fields, true)) {
                $orderedFields[] = $fieldName;
            }
        }

        // Добавляем оставшиеся поля, которых нет в order
        foreach ($fields as $fieldName) {
            if (!in_array($fieldName, $orderedFields, true)) {
                $orderedFields[] = $fieldName;
            }
        }

        foreach ($orderedFields as $fieldName) {
            $options = $this->commonOptions($fieldName);
            if (!in_array($fieldName, $ignoreFieldsList, true)
                && !in_array($fieldName, $hidden, true)
                && !$list->has($fieldName)
            ) {
                $list->add($fieldName, fieldDescriptionOptions: $options);
            }
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $ignoreFieldsList = $this->ignoreFiltersFieldsList;
        foreach ($this->entityFields() as $fieldName) {
            if (!in_array($fieldName, $ignoreFieldsList, true)) {
                $options = $this->commonOptions($fieldName);
                //                if (in_array($fieldName, ['id', 'name'], true)) {
                //                    $options['expanded'] = true;
                //                }
                $filter->add($fieldName, null, $options);
            }
        }
    }

    /**Метод генерации полей формы
     * @param FormMapper $form
     * @return void
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $ignoreFieldsList = $this->ignoreFormFieldsList;

        foreach ($this->entityFields() as $fieldName) {
            $options = $this->commonOptions($fieldName);
            if (!in_array($fieldName, $ignoreFieldsList, true)) {
                $form->add($fieldName, options: $options);
            }
        }
        foreach ($this->entityAssociations() as $association) {
            $targetEntity = $association['targetEntity'];
            $fieldName = $association['fieldName'];
            $options = $this->commonOptions($fieldName);
            if (in_array($fieldName, $ignoreFieldsList, true)) {
                continue;
            }

            if ($association['type'] & ClassMetadata::MANY_TO_MANY) {
                $options += [
                    'class' => $association->targetEntity,
                    'choice_label' => $this->getEntityRelationDisplayField($targetEntity),
                    'multiple' => true,
                    'query_builder' => fn ($er) => $er->createQueryBuilder('e')
                        ->orderBy('e.' . $this->getEntityRelationDisplayField($er->getClassName()), 'ASC'),
                ];

                switch ($this->formManyToManyPresentation) {
                    case FormManyToManyPresentation::CHECK_BOX:
                        $options['expanded'] = true;
                        break;
                    case FormManyToManyPresentation::SELECT:
                        $options['attr'] = [
                            'class' => 'select2',
                            'data-placeholder' => 'Выберите значения',
                        ];
                        break;
                    default:
                        throw new \InvalidArgumentException('Unknown formManyToManyPresentation type');
                }
                $form->add($fieldName, null, $options);
            } elseif ($association['type'] & ClassMetadata::ONE_TO_MANY) {
                $mapped = $association['mappedBy'];
                $ignoreFieldsList = $this->ignoreFormFieldsList;
                if ($mapped) {
                    $ignoreFieldsList[] = $mapped;
                }

                // Всю магию сделает DynamicEntityTypeExtension
                $options += [
                    'entry_options' => [
                        'data_class' => $targetEntity,
                        'exclude_fields' => $mapped ? $ignoreFieldsList + [$mapped] : $ignoreFieldsList,
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ];

                //               TODO может заменить на ModelType из Sonata
                $form->add($fieldName, CollectionType::class, $options);
            } elseif ($association['type'] & ClassMetadata::MANY_TO_ONE) {
                /***TODO  добавить проверку по исключению полей из под запроса */
                $relationPath = $fieldName; // например, clientPhone
                $nestedIgnores = array_filter($ignoreFieldsList, static fn ($f) => str_starts_with($f, $relationPath . '.'));

                $isNullableAssociation = $this->isNullableAssociation($association);
                $options += [
                    'class' => $association->targetEntity,
                    'choice_label' => $this->getEntityRelationDisplayField($targetEntity),
                    'multiple' => false,
                    'query_builder' => function ($er) {
                        $qb = $er->createQueryBuilder('e')
                            ->orderBy('e.' . $this->getEntityRelationDisplayField($er->getClassName()), 'ASC');
                        // отключаем join'ы, если в ignore указаны вложенные поля
                        //                        foreach ($nestedIgnores as $ignore) {
                        //                            $parts = explode('.', $ignore);
                        //                            if (count($parts) > 1) {
                        //                                $relationToAvoid = $parts[1];
                        //                                $qb->leftJoin('e.' . $relationToAvoid, $relationToAvoid)
                        //                                   ->addSelect($relationToAvoid); // Можно не добавлять, если хотите вообще исключить
                        //                                // Либо вообще не делать join
                        //                            }
                        //                        }

                        return $qb;
                    },
                    'attr' => [
                        'class' => 'select2',
                        'data-placeholder' => 'Выберите значения',
                        'required' => !$isNullableAssociation,
                    ],
                    'required' => !$isNullableAssociation,
                ];
                $form->add($fieldName, null, $options);
            }
            // Если СВЯЗЬ - не "ко многим", то пока что ничего не делаем, может потом добавится логика
        }
    }

    private function isNullableAssociation(AssociationMapping $association): bool
    {
        $isNullable = true;
        if (isset($association['joinColumns'])) {
            foreach ($association['joinColumns'] as $joinColumn) {
                if (isset($joinColumn['nullable']) && false === $joinColumn['nullable']) {
                    $isNullable = false;
                    break;
                }
            }
        }

        return $isNullable;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $ignoreShowList = $this->ignoreShowFieldsList;
        foreach ($this->entityFields() as $fieldName) {
            $options = $this->commonOptions($fieldName);
            if (!in_array($fieldName, $ignoreShowList, true)) {
                $show->add($fieldName, fieldDescriptionOptions: $options);
            }
        }
        /**TODO реализовать логику отображения связей*/
    }

    private function fieldVisibleName(string $fieldName): ?string
    {
        $labels = $this->fieldListNames;

        if (isset($labels[$fieldName])) {
            return $labels[$fieldName];
        }

        if (($translate = $this->translator->trans($fieldName)) && $translate !== $fieldName) {
            return $translate;
        }

        return null;
    }

    protected function commonOptions(string $fieldName): array
    {
        $options = [];
        $visibleFieldName = $this->fieldVisibleName($fieldName);
        if ($visibleFieldName) {
            $options['label'] = $visibleFieldName;
        }

        return $options;
    }

    /**
     * Определяет поле для отображения связи сущности в селекторе.
     */
    protected function getEntityRelationDisplayField(string $entityClass): string
    {
        $metadata = $this->entityManager->getClassMetadata($entityClass); // @phpstan-ignore-line
        $fields = $metadata->getFieldNames();

        // Приоритетный список полей для отображения
        $displayFields = ['name', 'title', 'label', 'username', 'email'];

        foreach ($displayFields as $field) {
            if (in_array($field, $fields, true)) {
                return $field;
            }
        }

        // Если не нашли подходящее поле, возвращаем первое строковое поле
        foreach ($fields as $field) {
            $type = $metadata->getTypeOfField($field);
            if ('string' === $type) {
                return $field;
            }
        }

        // Если нет строковых полей, возвращаем id
        return 'id';
    }

    /**Базовый метод сонаты*/
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
    }

    /**Метод должен возвращать ассоциативный массив вида ['field_name' => 'Название поля']
     * @return array
     */
    protected function fieldListNames(): array
    {
        return [];
    }

    /**Должен возвращать список исключаемых полей из таблицы
     * @return array
     */
    protected function ignoreTableFieldsList(): array
    {
        return array_merge(['delete', 'password'], self::TIME_FIELDS, self::USER_FIELDS);
    }

    /**Должен возвращать список исключаемых полей из формы
     * @return array
     */
    protected function ignoreFormFieldsList(): array
    {
        return array_merge(['id', 'delete', 'password'], self::TIME_FIELDS, self::USER_FIELDS);
    }

    protected function ignoreShowFieldsList(): array
    {
        return array_merge(['id', 'delete', 'password'], self::TIME_FIELDS, self::USER_FIELDS);
    }

    /**Должен возвращать список исключаемых полей из фильтров
     * @return array
     */

    protected function ignoreFiltersFieldsList(): array
    {
        return array_merge(['id', 'delete', 'password'], self::TIME_FIELDS, self::USER_FIELDS);
    }

    /**Метод для переопределения namespace операций для проверки доступа, если не задано то определяется из сущности*/
    public function capabilityNamespace(): ?string
    {
        return null;
    }

    public function toString(object $object): string
    {
        if (method_exists($object, '__toString') && null !== $object->__toString()) {
            return $object->__toString();
        }

        $id = method_exists($object, 'getId') && null !== $object->getId() ? $object->getId() : null;
        $idLink = '';
        if ($id) {
            $idLink = " (ID = $id)";
        }

        $name = $this->entityShortName();

        return mb_ucfirst($this->translator->trans(mb_strtolower($name))) . $idLink;
    }
}
