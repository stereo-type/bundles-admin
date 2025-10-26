<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle\Application\Service;

use Slcorp\RoleModelBundle\Application\Enum\CapabilityAction;
use Slcorp\RoleModelBundle\Application\Service\Operation\CapabilityServiceInterface as RoleCapabilityServiceInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;

interface CapabilityServiceInterface extends SecurityHandlerInterface, RoleCapabilityServiceInterface
{
    public function isGranted(AdminInterface $admin, $attributes, ?object $object = null): bool;

    public function capabilityActionCodeForEntity(object $object, CapabilityAction $action): string;

    public function capabilityActionCodeForClassname(string $className, CapabilityAction $action): string;

    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $entityReflection
     */
    public function capabilityActionCodeForReflection(\ReflectionClass $entityReflection, CapabilityAction $action): string;
}
