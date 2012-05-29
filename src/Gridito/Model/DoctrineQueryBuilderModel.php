<?php

namespace Gridito\Model;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Nette\ObjectMixin;

/**
 * Doctrine QueryBuilder model
 *
 * @author Jan Marek
 * @license MIT
 *
 * @property $items array
 */
class DoctrineQueryBuilderModel extends AbstractModel
{

    /** @var \Doctrine\ORM\QueryBuilder */
    protected $qb;

    /** @var array */
    protected $columnAliases = array();

    /**
     * Construct
     * @param \Doctrine\ORM\QueryBuilder query builder
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }


    /**
     * @return mixed
     */
    protected function _count()
    {
        $qb = clone $this->qb;
        $aliases = $qb->getRootAliases();
        $qb->select('count(' . $aliases[0] . ') fullcount');
        return $qb->getQuery()->getSingleResult(Query::HYDRATE_SINGLE_SCALAR);
    }


    /**
     * @return mixed
     */
    public function getItems()
    {
        $this->qb->setMaxResults($this->getLimit());
        $this->qb->setFirstResult($this->getOffset());

        list($sortColumn, $sortType) = $this->getSorting();
        if ($sortColumn) {
            if (isset($this->columnAliases[$sortColumn])) {
                $sortColumn = $this->columnAliases[$sortColumn]->qbName;
            } else {
                $aliases = $this->qb->getRootAliases();
                $sortColumn = $aliases[0] . '.' . $sortColumn;
            }
            $this->qb->orderBy($sortColumn, $sortType);
        }

        return $this->qb->getQuery()->getResult();
    }


    /**
     * @param $uniqueId
     * @return mixed
     */
    public function getItemByUniqueId($uniqueId)
    {
        $qb = clone $this->qb;
        $aliases = $qb->getRootAliases();
        return $qb->andWhere($aliases[0] . '.' . $this->getPrimaryKey() . ' = :gridprimarykey')->setParameter('gridprimarykey', $uniqueId)->getQuery()->getSingleResult();
    }


    /**
     * @param $item
     * @param $valueName
     * @return mixed
     */
    public function getItemValue($item, $valueName)
    {
        if (isset($this->columnAliases[$valueName])) {
            $getterPath = $this->columnAliases[$valueName]->getterPath;
        } else {
            $getterPath = $valueName;
        }

        $getters = explode('.', $getterPath);

        $value = $item;

        foreach ($getters as $getter) {
            $value = ObjectMixin::get($value, $getter);
        }

        return $value;
    }


    /**
     * @param string $columnName column name in gridito
     * @param string $getterPath name for getting a value for default renderer (e.g. "image.name" is translated to $entity->getImage()->getName())
     * @param string $qbName name for doctrine query builder (used for ordering)
     * @return \Gridito\Model\DoctrineQueryBuilderModel
     */
    public function addColumnAliases($columnName, $getterPath, $qbName)
    {
        $this->columnAliases[$columnName] = (object)array(
            'getterPath' => $getterPath,
            'qbName' => $qbName,
        );

        return $this;
    }
}