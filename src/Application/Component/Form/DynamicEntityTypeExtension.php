<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace AcademCity\AdminBundle\Application\Component\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicEntityTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [CollectionType::class]; // Расширяем CollectionType
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => DynamicEntityType::class,
        ]);

        $resolver->setNormalizer('entry_options', function (OptionsResolver $resolver, $entryOptions) {
            //            if (!isset($entryOptions['data_class'])) {
            //                throw new \InvalidArgumentException('data_class must be provided for DynamicEntityTypeExtension');
            //            }
            if (!isset($entryOptions['data_class']) || !$entryOptions['data_class']) {
                return []; // или просто не делать ничего
            }

            $entityClass = $entryOptions['data_class'];
            $excludeFields = $entryOptions['exclude_fields'] ?? [];

            $fields = (new \ReflectionClass($entityClass))->getProperties();
            $fields = array_filter($fields, static fn ($property) => !in_array($property->getName(), $excludeFields, true));
            $fields = array_map(static fn ($p) => $p->getName(), $fields);

            return [
                'data_class' => $entityClass,
                'fields' => $fields,
            ];
        });
    }
}
