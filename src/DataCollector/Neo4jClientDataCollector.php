<?php

namespace PandawanTechnology\Neo4jBundle\DataCollector;

use GraphAware\Neo4j\Client\Connection\ConnectionManager;
use PandawanTechnology\Neo4jBundle\Logger\QueryLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class Neo4jClientDataCollector extends DataCollector
{
    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var array
     */
    private $connectionNames;

    /**
     * @var QueryLogger
     */
    private $queryLogger;

    /**
     * @param ConnectionManager $connectionManager
     * @param array             $connectionNames
     * @param QueryLogger       $queryLogger
     */
    public function __construct(ConnectionManager $connectionManager, array $connectionNames, QueryLogger $queryLogger)
    {
        $this->connectionManager = $connectionManager;
        $this->connectionNames = $connectionNames;
        $this->queryLogger = $queryLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data['connections'] = $this->connectionNames;
        $this->data['nb_queries'] = count($this->queryLogger);
        $this->data['query_informations'] = $this->queryLogger->getStatements();
        $this->data['time'] = $this->queryLogger->getElapsedTime();
    }

    /**
     * @return int
     */
    public function getQueryCount()
    {
        return $this->data['nb_queries'];
    }

    /**
     * @return array
     */
    public function getConnections()
    {
        return $this->data['connections'];
    }

    /**
     * @return QueryLogger
     */
    public function getQueryInformations()
    {
        return $this->data['query_informations'];
    }

    /**
     * @return float
     */
    public function getTime()
    {
        return $this->data['time'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pandawan_technology_neo4j';
    }
}
