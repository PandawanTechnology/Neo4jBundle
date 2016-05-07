<?php

namespace PandawanTechnology\Neo4jBundle\EventSubscriber;

use GraphAware\Neo4j\Client\Event\PostRunEvent;
use GraphAware\Neo4j\Client\Event\PreRunEvent;
use GraphAware\Neo4j\Client\Neo4jClientEvents;
use PandawanTechnology\Neo4jBundle\Logger\QueryLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var QueryLogger
     */
    private $queryLogger;

    /**
     * @param QueryLogger $queryLogger
     */
    public function __construct(QueryLogger $queryLogger)
    {
        $this->queryLogger = $queryLogger;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Neo4jClientEvents::NEO4J_PRE_RUN => 'onPreRun',
            Neo4jClientEvents::NEO4J_POST_RUN => 'onPostRun',
        ];
    }

    /**
     * @param PreRunEvent $event
     */
    public function onPreRun(PreRunEvent $event)
    {
        foreach ($event->getStatements() as $statement) {
            $this->queryLogger->record($statement);
        }
    }

    /**
     * @param PostRunEvent $event
     */
    public function onPostRun(PostRunEvent $event)
    {
        foreach ($event->getResults() as $result) {
            $this->queryLogger->finish($result);
        }
    }
}
