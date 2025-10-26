<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle\Application\Component\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template TData
 * @template TValue
 * @template TTransformedValue
 *
 * @implements  DataTransformerInterface<TValue, TTransformedValue>
 *
 * @extends   AbstractType<TData>
 */
class TreeType extends AbstractType implements DataTransformerInterface
{
    /**
     * @return void
     *
     * @throws \JsonException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'tree',
            HiddenType::class,
            [
                'attr' => ['class' => 'tree-data'],
                'required' => false,
                'mapped' => false,
                'data' => json_encode($options['tree_data'], JSON_THROW_ON_ERROR),
            ]
        );
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'tree_options' => [
                'data_source' => '#',
                'item_id' => null,
            ],
            'tree_data' => [], // Опция теперь существует и по умолчанию пустая
        ]);
    }

    /**
     * @return void
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['tree_options'] = $options['tree_options'] ?? [];
        $view->vars['tree_data'] = $options['tree_data'] ?? [];
    }

    public function getBlockPrefix(): string
    {
        return 'tree';
    }

    /**TODO Не срабатывают трансформеры, в них приходит null, по этому трансформаия снаружи, там где форма формируется*/
    public function transform(mixed $value): mixed
    {
        if (null === $value) {
            return '';
        }

        // Преобразуем массив в JSON
        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        return (string) $value;
    }

    public function reverseTransform(mixed $value): mixed
    {
        if ('' === $value || null === $value) {
            return null;
        }

        // Преобразуем JSON в массив
        if (is_string($value)) {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        return $value;
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
