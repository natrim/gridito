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
        return FALSE;
    }

    /**
     * Set sortable
     * @param bool $sortable sortable
     * @throws \Nette\NotSupportedException
     */
    public function setSortable($sortable = TRUE)
    {
        $this->sortable = FALSE;
        throw new \Nette\NotSupportedException('Virtual columns cannot be sortable!');
    }

    /**
     * Get sorting
     * @return string|null asc, desc or null
     */
    public function getSorting()
    {
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
