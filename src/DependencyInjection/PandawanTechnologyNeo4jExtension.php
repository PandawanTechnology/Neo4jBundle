<?php

namespace PandawanTechnology\Neo4jBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PandawanTechnologyNeo4jExtension extends Extension
{
    const CONFIGURATION_KEY_NAME = 'pandawan_technology_neo4j.settings';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter(static::CONFIGURATION_KEY_NAME, $config['connections']);

        if ($container->hasParameter('kernel.debug') && true === $container->getParameter('kernel.debug')) {
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('debugging_services.xml');
        }
    }
}
