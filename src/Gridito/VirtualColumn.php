<?php

namespace Gridito;

/**
 * Grid column
 *
 * @author Jan Marek
 * @author Natrim
 * @license MIT
 *
 * @property $columnName string
 * @property $label string
 * @property $renderer callable
 * @property $length int
 * @property $type string
 * @property $format string
 * @property $sortable bool
 * @property $editable bool
 * @property $dateTimeFormat string
 * @property $dataGenerator mixed|callable
 *
 * @property-read $sorting null|string
 * @property-read $grid Grid
 *
 * @property-write $cellClass callable|string
 */
class VirtualColumn extends Column
{

    /**
     * Data generator
     * @var mixed|callable
     */
    private $dataGenerator;

    /** @var string */
    private $sortingColumnName;

    /**
     * @param mixed|callable $dataGenerator
     * @return \Gridito\VirtualColumn
     */
    public function setDataGenerator($dataGenerator)
    {
        $this->dataGenerator = $dataGenerator;
        return $this;
    }

    /**
     * @return mixed|callable
     */
    public function getDataGenerator()
    {
        return $this->dataGenerator;
    }

    /**
     * Is sortable?
     * @return bool
     */
    public function isSortable()
    {
        return ($this->sortable && !is_null($this->sortingColumnName));
    }

    /**
     * Set sortable
     * @param bool $sortable sortable
     * @param null $sortingColumnName
     * @throws \Nette\InvalidArgumentException
     * @return \Gridito\Column|\Gridito\VirtualColumn
     */
    public function setSortable($sortable = TRUE, $sortingColumnName = NULL)
    {
        $this->sortingColumnName = $sortingColumnName;

        if (is_null($this->sortingColumnName)) {
            throw new \Nette\InvalidArgumentException('Virtual column can be only sortable after setting column alias!');
        }

        parent::setSortable($sortable);
        return $this;
    }

    /**
     * Set column sorting alias
     * @param $name string|NULL
     * @return VirtualColumn
     */
    public function setSortingColumnName($name = NULL)
    {
        $this->sortingColumnName = $name;
        return $this;
    }

    /**
     * Gets the sorting alias
     * @return string
     */
    public function getSortingColumnName()
    {
        return $this->sortingColumnName;
    }


    /**
     * Get sorting
     * @return string|null asc, desc or null
     */
    public function getSorting()
    {
        $sorting = $this->getGrid()->getSorting();

        if ($sorting === NULL) {
            return NULL;
        }

        foreach ($sorting as $column => $type) {
            if ($column === $this->sortingColumnName) {
                return $type;
            }
        }

        return NULL;
    }

    /**
     * Set editable
     * @param bool $editable editable
     * @throws \Nette\NotSupportedException
     */
    public function setEditable($editable = TRUE)
    {
        $this->editable = FALSE;
        throw new \Nette\NotSupportedException('Virtual columns cannot be editable!');
    }

    /**
     * Is editable?
     * @return bool
     */
    public function isEditable()
    {
        return FALSE;
    }

    /**
     * Returns the value for this column from the row
     * @param $record mixed one row from which to get value
     * @return mixed
     */
    public function getColumnValue($record)
    {
        if (is_callable($this->dataGenerator)) {
            return call_user_func($this->dataGenerator, $record, $this->getColumnName());
        }

        return $this->dataGenerator;
    }
}
