<?php

namespace Gridito\Model;

use ArrayIterator;

/**
 * Abstract Gridito model
 *
 * @author Jan Marek
 * @author Natrim
 * @license MIT
 *
 * @property $limit int
 * @property $offset int
 * @property $primaryKey string
 *
 * @property-read $iterator \ArrayIterator
 * @property-read $sorting array
 */
abstract class AbstractModel implements IModel
{
    /** @var int */
    private $limit;

    /** @var int */
    private $offset;

    /** @var array */
    private $sorting = array();

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
     * @param $limit int
     * @return \Gridito\Model\AbstractModel
     */
    public function setLimit($limit)
    {
        $this->limit = (int)$limit;
        return $this;
    }


    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }


    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }


    /**
     * @param $offset int
     * @return \Gridito\Model\AbstractModel
     */
    public function setOffset($offset)
    {
        $this->offset = (int)$offset;
        return $this;
    }


    /**
     * Set sorting
     * @param string column
     * @param string asc or desc
     * @return \Gridito\Model\AbstractModel
     */
    public function setSorting($column, $type = self::ASC)
    {
        if (is_array($column)) {
            $this->sorting = $column;
        } else {
            $this->sorting[$column] = $type;
        }

        return $this;
    }

    /**
     * Remove sorting
     * @param string column
     * @return \Gridito\Model\AbstractModel
     * @throw InvalidArgumentException on wrong sort type
     */
    public function unsetSorting($column)
    {
        if (isset($this->sorting[$column])) {
            unset($this->sorting[$column]);
        }

        return $this;
    }


    /**
     * @return array
     */
    public function getSorting()
    {
        return $this->sorting;
    }


    /**
     * @param $name string
     * @return \Gridito\Model\AbstractModel
     */
    public function setPrimaryKey($name)
    {
        $this->primaryKey = $name;
        return $this;
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