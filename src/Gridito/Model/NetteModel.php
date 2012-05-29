<?php

namespace Gridito\Model;

use Nette\Database\Table\Selection;

/**
 * Nette\Database model
 *
 * @author Samuel Hapak
 * @license MIT
 *
 * @property $items array
 */
class NetteModel extends AbstractModel
{
    /** @var \Nette\Database\Table\Selection */
    private $selection;

    /**
     * Constructor
     * @param Selection $selection
     */
    public function __construct(Selection $selection)
    {
        $this->selection = $selection;
    }

    /**
     * @param $uniqueId
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getItemByUniqueId($uniqueId)
    {
        $select = clone $this->selection;
        return $select->where($this->getPrimaryKey(), $uniqueId)
            ->fetch();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $select = clone $this->selection;

        list($sortColumn, $sortType) = $this->getSorting();
        if ($sortColumn) {
            $select->order("$sortColumn $sortType");
        }
        return $select->limit($this->getLimit(), $this->getOffset())
            ->fetchPairs($this->getPrimaryKey());
    }


    /**
     * Item count
     * @return int
     */
    protected function _count()
    {
        return $this->selection->count('*');
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
