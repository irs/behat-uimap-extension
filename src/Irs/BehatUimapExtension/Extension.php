<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Behat\Behat\Extension\ExtensionInterface;

/**
 * UI map behat extension
 */
class Extension implements ExtensionInterface
{
    /**
     * Loads services definition to DI container
     *
     * @see \Behat\Behat\Extension\ExtensionInterface::load()
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('services.xml');
        $container->setParameter('irs.uimap.page.source.taf.paths', $config['uimaps']);
    }

    /**
     * Initializes config definition
     *
     * @see \Behat\Behat\Extension\ExtensionInterface::getConfig()
     */
    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->variableNode('uimaps')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->end();
    }

    /**
     * Returns compiler passes
     *
     * @see \Behat\Behat\Extension\ExtensionInterface::getCompilerPasses()
     */
    public function getCompilerPasses()
    {
        return array();
    }
}
