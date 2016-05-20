<?php

namespace PandawanTechnology\Neo4jBundle\Form\ChoiceList;

class IdReader
{
    /**
     * @param mixed $object
     *
     * @return int|null
     */
    public function getIdValue($object)
    {
        if (!$object) {
            return;
        }

        return $object->identity();
    }
}
