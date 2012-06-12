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

        if (count($this->getSorting()) > 0) {
            $this->_sort($data);
        }

        return $data;
    }

    /**
     * Sort the array
     * @param array $data
     * @return array
     */
    private function _sort(array $data)
    {
        $result = array();

        //prepare temporary arrays
        $sortParams = array();
        foreach ($this->getSorting() as $sortColumn => $sortType) {
            foreach ($data as $id => $row) {
                $sortParams[$sortColumn][$id] = $row[$sortColumn];
            }

            $sortParams[] = ((is_string($sortType) && strncasecmp($sortType, 'd', 1)) || $sortType > 0 ? SORT_ASC : SORT_DESC);
        }

        $sortParams[] = &$data;
        call_user_func_array('array_multisort', $sortParams);

        //clean
        unset($sortParams);

        //return back the id
        foreach ($data as $row) {
            $result[$row[$this->getPrimaryKey()]] = $row;
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