<?php

namespace Gridito\Model;

use ArrayIterator;

/**
 * Abstract Gridito model
 *
 * @author Jan Marek
 * @author Natrim
 * @license MIT
 */
abstract class AbstractModel implements IModel
{
    /** @var int */
    private $limit;

    /** @var int */
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
     * @param $limit int
     * @return \Gridito\Model\AbstractModel
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
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
        $this->offset = $offset;
        return $this;
    }


    /**
     * Set sorting
     * @param string column
     * @param string asc or desc
     * @return \Gridito\Model\AbstractModel
     * @throw InvalidArgumentException on wrong sort type
     */
    public function setSorting($column, $type)
    {
        if ($type !== self::ASC && $type !== self::DESC) {
            throw new \InvalidArgumentException('Wrong sorting type! Use Gridito\Model\IModel::ASC or Gridito\Model\IModel::DESC !');
        }

        $this->sorting = array($column, $type);

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
     * @param $name
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