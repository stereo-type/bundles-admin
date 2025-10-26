# СОЗДАНИЕ СПРАВОЧНИКА
1) Создать класс-наследник ```Slcorp\AdminBundle\Application\Component\BaseAdmin```
2) Прописать его в сервисе в формате 
```
    admin.users:
      class: App\Features\Core\Application\Service\Catalogs\Admin\UserAdmin
      tags:
        - { name: sonata.admin, model_class: Slcorp\RoleModelBundle\Domain\Entity\User, manager_type: orm, label: "Users" }
```
3) На странице дашборда админки (по умолчанию /admin) появится справочник


# КАСТОМИЗАЦИЯ СПРАВОЧНИКА

1) Метод  ```configureListFields(ListMapper $list)``` определяет столбцы отчета для вывода, по умолчанию берутся все поля из сущности
2) В базовом классе определены методы с преднастройками для исключения системных полей от пользователя в таблице, фильтрах и форме, при неободимости их переопределеить или расширить в своем классе. `ignoreTableFieldsList`, `ignoreFormFieldsList`, `ignoreFiltersFieldsList`
3) Метод `fieldListNames` Названия для полей выводимые в справочнике и в форме


