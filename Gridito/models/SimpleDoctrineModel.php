<?php

namespace Gridito\Models;

use Doctrine\ORM\EntityManager;

/**
 * Simple Doctrine model
 *
 * @author Jan Marek
 */
class SimpleDoctrineModel extends DoctrineQueryBuilderModel
{
    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param $entityName
     */
    public function __construct(EntityManager $em, $entityName)
    {
        parent::__construct($em->getRepository($entityName)->createQueryBuilder('e'));
        $this->setPrimaryKey($em->getClassMetadata($entityName)->getSingleIdentifierFieldName());
    }
}