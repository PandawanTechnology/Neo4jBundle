<?php

namespace PandawanTechnology\Neo4jBundle\Tests\EventSubscriber;

use GraphAware\Neo4j\Client\Neo4jClientEvents;
use PandawanTechnology\Neo4jBundle\EventSubscriber\LoggerSubscriber;

class LoggerSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected $queryLogger;

    /**
     * @var LoggerSubscriber
     */
    protected $loggerSubscriber;

    protected function setUp()
    {
        $this->queryLogger = $this->getMock('PandawanTechnology\Neo4jBundle\Logger\QueryLogger');

        $this->loggerSubscriber = new LoggerSubscriber($this->queryLogger);
    }

    public function testGetSubscribedEvents()
    {
        $events = LoggerSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(Neo4jClientEvents::NEO4J_PRE_RUN, $events);
        $this->assertArrayHasKey(Neo4jClientEvents::NEO4J_POST_RUN, $events);
    }

    public function testOnPreRunNoStatements()
    {
        $this->queryLogger->expects($this->never())
            ->method('record');

        $event = $this->getMockBuilder('GraphAware\Neo4j\Client\Event\PreRunEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getStatements')
            ->will($this->returnValue([]));

        $this->loggerSubscriber->onPreRun($event);
    }

    public function testOnPreRun()
    {
        $statement1 = $this->getMock('GraphAware\Common\Cypher\StatementInterface');
        $statement2 = $this->getMock('GraphAware\Common\Cypher\StatementInterface');
        $this->queryLogger->expects($this->exactly(2))
            ->method('record');

        $event = $this->getMockBuilder('GraphAware\Neo4j\Client\Event\PreRunEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getStatements')
            ->will($this->returnValue([$statement1, $statement2]));

        $this->loggerSubscriber->onPreRun($event);
    }

    public function testOnPostRunNoStatements()
    {
        $this->queryLogger->expects($this->never())
            ->method('finish');

        $event = $this->getMockBuilder('GraphAware\Neo4j\Client\Event\PostRunEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getResults')
            ->will($this->returnValue([]));

        $this->loggerSubscriber->onPostRun($event);
    }

    public function testOnPostRun()
    {
        $result1 = $this->getMock('GraphAware\Common\Result\StatementResult');
        $result2 = $this->getMock('GraphAware\Common\Result\StatementResult');
        $this->queryLogger->expects($this->exactly(2))
            ->method('finish');

        $event = $this->getMockBuilder('GraphAware\Neo4j\Client\Event\PostRunEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getResults')
            ->will($this->returnValue([$result1, $result2]));

        $this->loggerSubscriber->onPostRun($event);
    }
}
