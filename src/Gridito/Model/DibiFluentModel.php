<?php

namespace Gridito\Model;

use DibiFluent;

/**
 * DibiFluent model
 *
 * @author Jan Marek
 * @license MIT
 *
 * @property $items array
 */
class DibiFluentModel extends AbstractModel
{
    /** @var DibiFluent */
    protected $fluent;

    /** @var string */
    protected $rowClass;


    /**
     * Constructor
     * @param DibiFluent dibi fluent object
     * @param string row class name
     */
    public function __construct(DibiFluent $fluent, $rowClass = 'DibiRow')
    {
        $this->fluent = $fluent;
        $this->rowClass = $rowClass;
    }

    /**
     * @param $uniqueId
     * @return mixed
     */
    public function getItemByUniqueId($uniqueId)
    {
        $fluent = clone $this->fluent;
        $fluent->where('%n =', $this->getPrimaryKey(), $uniqueId);
        return $fluent->execute()->setRowClass($this->rowClass)->fetch();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $fluent = clone $this->fluent;

        $fluent->limit($this->getLimit());
        $fluent->offset($this->getOffset());

        if (count($this->getSorting()) > 0) {
            $fluent->orderBy($this->getSorting());
        }

        return $fluent->execute()->setRowClass($this->rowClass)->fetchAll();
    }


    /**
     * Item count
     * @return int
     */
    protected function _count()
    {
        return $this->fluent->count();
    }

    /**
     * @param $item
     * @param $valueName
     * @return mixed
     */
    public function getItemValue($item, $valueName)
    {
        return $item->$valueName;
    }
}