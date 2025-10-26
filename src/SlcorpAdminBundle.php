<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle;

use Slcorp\AdminBundle\DependencyInjection\Compiler\TwigStringExtensionCompilerPass;
use Slcorp\AdminBundle\DependencyInjection\SlcorpAdminExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SlcorpAdminBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new TwigStringExtensionCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new SlcorpAdminExtension();
    }
}
