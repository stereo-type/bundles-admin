<?php

/**
 * @copyright  2025 Zhalayletdinov Vyacheslav evil_tut@mail.ru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace Slcorp\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('academ_city_admin');

        $treeBuilder->getRootNode()
                    ->children()
                        ->arrayNode('dashboard')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->scalarNode('title')->defaultValue('Admin Panel')->end()
                                ->scalarNode('logo')->defaultValue('/bundles/sonataadmin/images/logo_title.png')->end()
                                ->scalarNode('favicon')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end();

        return $treeBuilder;
    }
}
