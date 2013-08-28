<?php

namespace Gridito;

use Nette\NotImplementedException;
use Nette\Utils\Html;

class CheckboxColumn extends Column
{
    const RENDER_CHECKBOX = ":checkbox";

    /** @var string */
    private $type = self::RENDER_CHECKBOX;

    /** @var bool */
    private $editable = TRUE;

    /**
     * Set cell renderer
     * @param callback cell renderer
     * @param int cell renderer number of parameters to put
     * @return Column
     */
    public function setRenderer($cellRenderer, $rendererParamsNum = 3)
    {
        throw new NotImplementedException('Checkbox Column does not support setting custom renderer!');
    }

    /**
     * Get the type of cell
     * @return string type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set editable
     * @param bool editable
     * @return Column
     */
    public function setEditable($editable = TRUE)
    {
        $this->editable = $editable;
        return $this;
    }

    /**
     * Is editable?
     * @return bool
     */
    public function isEditable()
    {
        return $this->editable;
    }

    /**
     * Set the type of cell
     * @param string type
     * @throw \InvalidArgumentException
     * @return Column
     */
    public function setType($type)
    {
        throw new NotImplementedException('Checkbox Column does not support setting type!');
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
     * @param bool sortable
     * @return Column
     */
    public function setSortable($sortable = TRUE)
    {
        throw new NotImplementedException('Checkbox Column does not support sorting!');
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
     * Render cell
     * @param mixed record
     */
    public function renderCell($record)
    {
        $value = $this->getGrid()->getModel()->getUniqueId($record);

        $column = Html::el('input', array('type' => 'checkbox', 'id' => $this->columnName . $value, 'name' => $this->columnName . '[]', 'value' => (string)$value));

        if (!$this->editable) {
            $column->disabled = 'disabled';
        }
        echo $column;
    }

    /**
     * Gets cell class
     * @param $iterator
     * @param $row
     * @return string
     */
    public function getCellClass($iterator, $row)
    {
        return str_replace('gridito-cell', 'gridito-checkboxcell', parent::getCellClass($iterator, $row));
    }

    /**
     * Returns the value for this column from the row
     * @param $record mixed one row from which to get value
     * @return mixed
     */
    public function getColumnValue($record)
    {
        return null;
    }
}