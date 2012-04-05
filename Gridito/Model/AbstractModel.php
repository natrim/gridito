<?php

namespace Gridito\Model;

use ArrayIterator;

/**
 * Abstract Gridito model
 *
 * @author Jan Marek
 * @license MIT
 */
abstract class AbstractModel implements IModel
{
    /** @var array */
    private $limit;

    /** @var array */
    private $offset;

    /** @var array */
    private $sorting = array(null, null);

    /** @var string */
    private $primaryKey = 'id';

    /** @var int */
    private $count = null;


    /**
     * @abstract
     *
     */
    abstract protected function _count();


    /**
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }


    /**
     * @return array
     */
    public function getLimit()
    {
        return $this->limit;
    }


    /**
     * @return array
     */
    public function getOffset()
    {
        return $this->offset;
    }


    /**
     * @param $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }


    /**
     * Set sorting
     * @param string column
     * @param string asc or desc
     */
    public function setSorting($column, $type)
    {
        return $this->sorting = array($column, $type);
    }


    /**
     * @return array
     */
    public function getSorting()
    {
        return $this->sorting;
    }


    /**
     * @param $name
     */
    public function setPrimaryKey($name)
    {
        $this->primaryKey = $name;
    }


    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }


    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getItems());
    }


    /**
     * @param $item
     * @return mixed
     */
    public function getUniqueId($item)
    {
        return $item->{$this->getPrimaryKey()};
    }


    /**
     * @param array $uniqueIds
     * @return array
     */
    public function getItemsByUniqueIds(array $uniqueIds)
    {
        return array_map(array($this, 'getItemByUniqueId'), $uniqueIds);
    }


    /**
     * @return int|null
     */
    public function count()
    {
        if ($this->count === null) {
            $this->count = $this->_count();
        }

        return $this->count;
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