<?php

namespace PandawanTechnology\Neo4jBundle\DependencyInjection;

use GraphAware\Neo4j\Client\ClientBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pandawan_technology_neo4j');

        $rootNode->children()
             ->arrayNode('connections')
             ->requiresAtLeastOneElement()
             ->useAttributeAsKey('name')
             ->prototype('array')
             ->children()
                 ->scalarNode('uri')->isRequired()->cannotBeEmpty()->end()
                 ->integerNode('timeout')->defaultValue(ClientBuilder::DEFAULT_TIMEOUT)->end()
                 ->booleanNode('master')->defaultTrue()->end()
             ->end()
             ->end()
        ;

        return $treeBuilder;
    }
}
