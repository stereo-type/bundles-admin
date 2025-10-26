<?php

/**
 * @copyright  2024 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
declare(strict_types=1);

namespace Slcorp\AdminBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;

class SlcorpAdminExtension extends Extension implements PrependExtensionInterface
{
    private const PERMISSIONS_MASK = 0755;

    private Filesystem $filesystem;
    private string $projectRoot;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->filesystem = new Filesystem();
        $this->projectRoot = $container->getParameter('kernel.project_dir');

        $container->setParameter('slcorp_admin.dashboard.title', $config['dashboard']['title']);
        $container->setParameter('slcorp_admin.dashboard.logo', $config['dashboard']['logo']);
        $container->setParameter('slcorp_admin.dashboard.favicon', $config['dashboard']['favicon']);

        $this->addDoctrineMappings($container);
        $this->addDoctrineMigrations($container);
        $this->addRoutes($container);
    }

    public function prepend(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config/packages'));
        $loader->load('sonata_admin.yaml');
        $loader->load('sonata_block.yaml');
        $loader->load('sonata_form.yaml');
        $loader->load('twig.yaml');
    }

    private function addDoctrineMappings(ContainerBuilder $container): void
    {
        $subDir = '/config/packages/doctrine';
        $filename = 'admin_bundle.php';
        $this->createConfigsFile($subDir, $filename);
    }

    private function addDoctrineMigrations(ContainerBuilder $container): void
    {
        $subDir = '/config/packages/doctrine_migrations';
        $filename = 'admin_bundle.php';
        $this->createConfigsFile($subDir, $filename);
    }

    private function addRoutes(ContainerBuilder $container): void
    {
        $subDir = '/config/packages/routes';
        $filename = 'admin_bundle.php';
        $this->createConfigsFile($subDir, $filename);
    }

    private function createConfigsFile(string $subDir, string $filename, bool $remove = true): void
    {
        $projectConfigDir = $this->projectRoot . $subDir;
        $bundleMappingFile = __DIR__ . "/../..$subDir/$filename";
        $targetMappingFile = $projectConfigDir . "/$filename";

        if (!$this->filesystem->exists($projectConfigDir)) {
            $this->filesystem->mkdir($projectConfigDir, self::PERMISSIONS_MASK);
        }

        if (!$this->filesystem->exists($targetMappingFile)) {
            $this->filesystem->copy($bundleMappingFile, $targetMappingFile);
        } elseif ($remove) {
            $this->filesystem->remove($targetMappingFile);
            $this->filesystem->copy($bundleMappingFile, $targetMappingFile);
        }
    }
}
