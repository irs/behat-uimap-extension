<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementExtensionInterface()
    {
        $this->assertInstanceOf(\Behat\Testwork\ServiceContainer\Extension::class, new Extension);
    }

    public function testLoadShouldAddPropelyConfiguredUimapSelectorToDiContainer()
    {
        // preapre
        $extension = new Extension;
        $container = new ContainerBuilder;
        $config = array(
            'uimaps' => array (
                ''       => uniqid(),
                'admin/' => uniqid(),
            ),
        );

        // act
        $extension->load($container, $config);

        // assert
        $this->assertNotEmpty($container->findTaggedServiceIds('behat.mink.selector'));
    }

    /**
     * @dataProvider providerCorrectConfigs
     */
    public function testCorrectConfigsShouldFollowDefinition(array $sourceConfig, array $expectedConfig)
    {
        // preapre
        $builder = new TreeBuilder;
        $builder->root('extensions')
            ->append($extensionNode = new ArrayNodeDefinition('\Irs\BehatUimapExtension\Extension'));
        $processor = new Processor;
        $extension = new Extension;

        // act
        $extension->configure($extensionNode);
        $processedConfig = $processor->processConfiguration(new ConfigurationTester($builder), $sourceConfig);

        // assert
        $this->assertEquals($expectedConfig, $processedConfig);
    }

    /**
     * @dataProvider providerIncorrectConfigs
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testIncorrectConfigsShouldNotFollowDefinition(array $sourceConfig)
    {
        // preapre
        $builder = new TreeBuilder;
        $builder->root('extensions')
            ->append($node = new ArrayNodeDefinition('\Irs\BehatUimapExtension\Extension'));
        $processor = new Processor;
        $extension = new Extension;

        // act
        $extension->configure($node);
        $processor->processConfiguration(new ConfigurationTester($builder), $sourceConfig);
    }

    public function providerCorrectConfigs()
    {
        return array(
            array(
                array(
                    'extensions' => array(
                        '\Irs\BehatUimapExtension\Extension' => array(
                                'uimaps' => array(
                                    ''       => 'uimaps/frontend',
                                    'admin/' => 'uimaps/backend',
                            ),
                        ),
                    ),
                ),
                array(
                    '\Irs\BehatUimapExtension\Extension' => array(
                        'uimaps' => array(
                            ''       => 'uimaps/frontend',
                            'admin/' => 'uimaps/backend',
                        ),
                    ),
                ),
            ),
        );
    }

    public function providerIncorrectConfigs()
    {
        return array(
            array(
                array(
                    'extensions' => array(
                        '\Irs\BehatUimapExtension\Extension' => array(),
                    ),
                ),
                array(
                    '\Irs\BehatUimapExtension\Extension' => array(
                        'extensions' => array(
                        '\Irs\BehatUimapExtension\Extension' => array(
                                'uimaps' => array(),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}

class ConfigurationTester implements ConfigurationInterface
{
    private $_builder;

    public function __construct(TreeBuilder $builder)
    {
        $this->_builder = $builder;
    }

    public function getConfigTreeBuilder()
    {
        return $this->_builder;
    }
}