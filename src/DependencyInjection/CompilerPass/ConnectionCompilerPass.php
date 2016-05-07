<?php

namespace PandawanTechnology\Neo4jBundle\DependencyInjection\CompilerPass;

use PandawanTechnology\Neo4jBundle\DependencyInjection\PandawanTechnologyNeo4jExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ConnectionCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$config = $container->getParameter(PandawanTechnologyNeo4jExtension::CONFIGURATION_KEY_NAME)) {
            return;
        }

        $connectionManager = new Definition('GraphAware\Neo4j\Client\Connection\ConnectionManager');
        $container->setDefinition('pandawan_technology_neo4j.connection_manager', $connectionManager);
        $connectionNames = [];

        foreach ($config as $name => $settings) {
            $connectionDefinition = new Definition('GraphAware\Neo4j\Client\Connection\Connection', [
                $name,
                $settings['uri'],
                null,
                $settings['timeout'],
            ]);
            $connectionDefinition->setPublic(false);
            $connectionServiceName = 'pandawan_technology_neo4j.connections.'.$name;
            $container->setDefinition($connectionServiceName, $connectionDefinition);

            $connectionManager->addMethodCall('registerExistingConnection', [$name, $connectionDefinition]);

            if (true === $settings['master']) {
                $connectionManager->addMethodCall('setMaster', [$name]);
            }

            $connectionNames[$name] = $connectionServiceName;
        }

        try {
            $eventDispatcher = $container->findDefinition('event_dispatcher');
        } catch (ServiceNotFoundException $e) {
            $eventDispatcher = null;
        }

        $neo4jClient = new Definition('GraphAware\Neo4j\Client\Client', [$connectionManager, $eventDispatcher]);
        $container->setDefinition('pandawan_technology_neo4j.client', $neo4jClient);
        $container->setParameter('pandawan_technology_neo4j.connection_names', $connectionNames);
    }
}
