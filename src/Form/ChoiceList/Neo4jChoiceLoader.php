<?php

namespace PandawanTechnology\Neo4jBundle\Form\ChoiceList;

use GraphAware\Neo4j\Client\Client as Neo4jClient;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class Neo4jChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * @var Neo4jClient
     */
    private $neo4jClient;

    /**
     * @var ChoiceListFactoryInterface
     */
    private $choiceListFactory;

    /**
     * @var string
     */
    private $queryStatement;

    /**
     * @var ChoiceListInterface
     */
    private $choiceList;

    /**
     * @var string
     */
    private $recordAlias;

    /**
     * @var IdReader
     */
    private $idReader;

    /**
     * @param Neo4jClient                $neo4jClient
     * @param ChoiceListFactoryInterface $choiceListFactory
     * @param string                     $queryStatement
     * @param string                     $recordAlias
     * @param IdReader                   $idReader
     */
    public function __construct(
        Neo4jClient $neo4jClient,
        ChoiceListFactoryInterface $choiceListFactory,
        $queryStatement,
        $recordAlias,
        IdReader $idReader
    ) {
        $this->neo4jClient = $neo4jClient;
        $this->choiceListFactory = $choiceListFactory;
        $this->queryStatement = $queryStatement;
        $this->recordAlias = $recordAlias;
        $this->idReader = $idReader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceList($value = null)
    {
        if ($this->choiceList) {
            return $this->choiceList;
        }

        $recordsResult = [];
        $records = $this->neo4jClient->run($this->queryStatement)->records();

        foreach ($records as $record) {
            $recordsResult[] = $record->get($this->recordAlias);
        }

        $this->choiceList = $this->choiceListFactory->createListFromChoices(
            $recordsResult,
            $value
        );

        return $this->choiceList;
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        if (empty($values)) {
            return [];
        }

        // Optimize performance in case we have an object loader and
        // a single-field identifier
        $optimize = null === $value || is_array($value) && $value[0] === $this->idReader;

        if ($optimize && !$this->choiceList) {
            $unorderedObjects = $this->getItemById($values);
            $objectsById = $objects = [];

            // Maintain order and indices from the given $values
            // An alternative approach to the following loop is to add the
            // "INDEX BY" clause to the Doctrine query in the loader,
            // but I'm not sure whether that's doable in a generic fashion.
            foreach ($unorderedObjects as $object) {
                $objectsById[(string) $this->idReader->getIdValue($object)] = $object;
            }

            foreach ($values as $i => $id) {
                if (isset($objectsById[$id])) {
                    $objects[$i] = $objectsById[$id];
                }
            }

            return $objects;
        }

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    /**
     * {@inheritdoc}
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        // Performance optimization
        if (empty($choices)) {
            return [];
        }

        // Optimize performance for single-field identifiers. We already
        // know that the IDs are used as values

        // Attention: This optimization does not check choices for existence
        if (!$this->choiceList) {
            $values = [];

            // Maintain order and indices of the given objects
            foreach ($choices as $i => $object) {
                $values[$i] = (string) $this->idReader->getIdValue($object);
            }

            return $values;
        }

        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    private function getItemById(array $ids)
    {
        $ids = array_map('intval', $ids);

        try {
            $stmtResult = $this->neo4jClient->run('MATCH (n) WHERE ID(n) IN {ids} RETURN n', ['ids' => $ids]);
        } catch (\Exception $e) {
            return [];
        }

        $result = [];

        if (!$stmtResult->size()) {
            return $result;
        }

        foreach ($stmtResult->records() as $record) {
            $result[] = $record->get('n');
        }

        return $result;
    }
}
