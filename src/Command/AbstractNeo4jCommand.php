<?php

namespace PandawanTechnology\Neo4jBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AbstractNeo4jCommand extends ContainerAwareCommand
{
    /**
     * @param string $alias
     *
     * @return \GraphAware\Neo4j\Client\Connection\Connection
     *
     * @throws \InvalidArgumentException
     */
    protected function getNeo4jConnection($alias)
    {
        return $this->getNeo4jConnectionManager()->getConnection($alias);
    }

    /**
     * @return \GraphAware\Neo4j\Client\Connection\Connection|null
     */
    protected function getNeo4jMasterConnection()
    {
        return $this->getNeo4jConnectionManager()->getMasterConnection();
    }

    /**
     * @return \GraphAware\Neo4j\Client\Connection\ConnectionManager
     */
    protected function getNeo4jConnectionManager()
    {
        return $this->getContainer()->get('pandawan_technology_neo4j.connection_manager');
    }
}
