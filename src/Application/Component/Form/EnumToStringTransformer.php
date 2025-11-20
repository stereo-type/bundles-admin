<?php

declare(strict_types=1);

namespace Slcorp\AdminBundle\Application\Component\Form;

use PHPStan\DependencyInjection\NonAutowiredService;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @phpstan-implements DataTransformerInterface<\UnitEnum, string>
 */
#[NonAutowiredService('enum.to.string.transformer')]
class EnumToStringTransformer implements DataTransformerInterface
{
    private string $enumClass;

    public function __construct(string $enumClass)
    {
        $this->enumClass = $enumClass;
    }

    /**
     * Преобразует Enum в строку (для отображения в форме).
     */
    public function transform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        return property_exists($value, 'value') ? $value->value : $value->name;
    }

    /**
     * Преобразует строку в Enum (при сохранении).
     */
    public function reverseTransform($value): ?\UnitEnum
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if ($value instanceof \UnitEnum) {
            return $value;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        try {
            if (method_exists($this->enumClass, 'from')) {
                return $this->enumClass::from($value);
            }

            if (method_exists($this->enumClass, 'tryFrom')) {
                $enum = $this->enumClass::tryFrom($value);
                if (null === $enum) {
                    throw new TransformationFailedException(sprintf('Invalid enum value "%s"', $value));
                }

                return $enum;
            }

            // Для pure enum ищем по name
            $cases = $this->enumClass::cases();
            foreach ($cases as $case) {
                if ($case->name === $value) {
                    return $case;
                }
            }
            throw new TransformationFailedException(sprintf('Invalid enum name "%s"', $value));
        } catch (\ValueError $e) {
            throw new TransformationFailedException(sprintf('Invalid enum value "%s": %s', $value, $e->getMessage()));
        }
    }
}
