<?php

namespace PandawanTechnology\Neo4jBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PandawanTechnologyNeo4jExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $this->registerConnections($container, $config);
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @param array            $config
     */
    private function registerConnections(ContainerBuilder $containerBuilder, array $config)
    {
        $connectionManager = new Definition('GraphAware\Neo4j\Client\Connection\ConnectionManager');
        $containerBuilder->setDefinition('pandawan_technology_neo4j.connection_manager', $connectionManager);

        foreach ($config['connections'] as $name => $settings) {
            $connectionDefinition = new Definition('GraphAware\Neo4j\Client\Connection\Connection', [
                $name,
                $settings['uri'],
                null,
                $settings['timeout'],
            ]);
            $connectionDefinition->setPublic(false);
            $containerBuilder->setDefinition('pandawan_technology_neo4j.connections.'.$name, $connectionDefinition);

            $connectionManager->addMethodCall('registerExistingConnection', [$name, $connectionDefinition]);

            if (true === $settings['master']) {
                $connectionManager->addMethodCall('setMaster', [$name]);
            }
        }

        $neo4jClient = new Definition('GraphAware\Neo4j\Client\Client', [$connectionManager]);
        $containerBuilder->setDefinition('pandawan_technology_neo4j.client', $neo4jClient);
    }
}
