<?php

namespace Gridito\Model;

/**
 * Array model
 *
 * @author Natrim
 * @license MIT
 *
 * @property $items array
 */
class ArrayModel extends AbstractModel
{
    /** @var array */
    protected $data;

    /** @var array */
    private $_data;


    /**
     * Constructor
     * @param array $data array with data
     */
    public function __construct(array $data)
    {
        $this->_data = $data;
        $this->data = $data;
    }

    /**
     * Sets new primary key - if new key not exists in the row you will lose data! luckily key 'id' will reset to old array .)
     * @param $name
     * @return \Gridito\Model\ArrayModel
     */
    public function setPrimaryKey($name)
    {

        if ($name !== $this->getPrimaryKey()) {
            if ($name === 'id') {
                $this->data = $this->_data;
            } else {
                //rebase data on new key
                $data = array();
                foreach ($this->_data as $row) {
                    if (isset($row[$name])) {
                        $data[$row[$name]] = $row;
                    }
                }
                $this->data = $data;
            }
        }

        parent::setPrimaryKey($name);

        return $this;
    }

    /**
     * @param $uniqueId
     * @return null|array
     */
    public function getItemByUniqueId($uniqueId)
    {
        if (!array_key_exists($uniqueId, $this->data)) {
            return NULL;
        }

        return $this->data[$uniqueId];
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $data = array_slice($this->data, (int)$this->getOffset(), $this->getLimit(), TRUE);

        list($sortColumn, $sortType) = $this->getSorting();

        if ($sortColumn) {
            $data = $this->_sort($data, $sortColumn, ($sortType === self::DESC ? FALSE : TRUE));
        }

        return $data;
    }

    /**
     * Sort the array by key
     * taken from php.net
     * @param array $array
     * @param $key
     * @param bool $asc
     * @return array
     */
    private function _sort(array $array, $key, $asc = TRUE)
    {
        $result = array();

        $values = array();
        foreach ($array as $id => $value) {
            $values[$id] = isset($value[$key]) ? $value[$key] : '';
        }

        if ($asc) {
            asort($values);
        }
        else {
            arsort($values);
        }

        foreach ($values as $key => $value) {
            $result[$key] = $array[$key];
        }

        return $result;
    }

    /**
     * Item count
     * @return int
     */
    protected function _count()
    {
        return count($this->data);
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getUniqueId($item)
    {
        return $item[$this->getPrimaryKey()];
    }

    /**
     * @param $item
     * @param $valueName
     * @return mixed
     */
    public function getItemValue($item, $valueName)
    {
        return $item[$valueName];
    }
}