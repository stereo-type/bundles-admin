<?php

declare(strict_types=1);

namespace Slcorp\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final class TwigStringExtensionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has('twig.loader.native_filesystem')) {
            $loaderDefinition = $container->getDefinition('twig.loader.native_filesystem');
            $loaderDefinition->addMethodCall('addPath', [dirname(__DIR__, 3) . '/templates', 'SlcorpAdmin']);
        }
    }
}
