<?php

namespace Gridito;
use Nette\Utils\Strings;
use Nette\Utils\Html;

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
 *
 * @property-read $sorting null|string
 * @property-read $grid Grid
 *
 * @property-write $cellClass callable|string
 */
class Column extends \Nette\Application\UI\Control
{

    const RENDER_STRING = ':string';
    const RENDER_EMAIL = ':email';
    const RENDER_BOOL = ':boolean';
    const RENDER_DATE = ':date';
    const RENDER_ARRAY = ':array';

    /** @var string */
    private $label;

    /** @var callback */
    private $renderer = null;

    /** @var int */
    private $rendererParamsNum = 3;

    /** @var int */
    private $maxlen = null;

    /** @var string */
    private $type = self::RENDER_STRING;

    /** @var bool */
    private $sortable = false;

    /** @var bool */
    private $editable = false;

    /** @var string */
    private $dateTimeFormat = 'j.n.Y G:i';

    /** @var string|callable */
    private $cellClass = null;

    /** @var string */
    private $format = null;

    /** @var string */
    public $spamProtection = null;

    /** @var string Array delimiter on renderArray */
    public $arrayDelimiter = ", ";

    /** @var string */
    private $columnName;


    public function setCellClass($class)
    {
        $this->cellClass = $class;
        return $this;
    }


    public function getCellClass($iterator, $row)
    {
        if (is_callable($this->cellClass)) {
            return call_user_func($this->cellClass, $iterator, $row);
        } elseif (is_string($this->cellClass)) {
            return $this->cellClass;
        } else {
            return null;
        }
    }


    /**
     * Get label
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }


    /**
     * Set label
     * @param string label
     * @return Column
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }


    /**
     * Get cell renderer
     * @return callback
     */
    public function getRenderer()
    {
        return $this->renderer;
    }


    /**
     * Set cell renderer
     * @param callback cell renderer
     * @param int cell renderer number of parameters to put
     * @return Column
     */
    public function setRenderer($cellRenderer, $rendererParamsNum = 3)
    {
        $this->renderer = $cellRenderer;
        $this->rendererParamsNum = $rendererParamsNum;
        return $this;
    }

    /**
     * Set maximal length of cell
     * @param $maxlen int
     * @return Column
     */
    public function setLength($maxlen)
    {
        $this->maxlen = (int)$maxlen;
        return $this;
    }

    /**
     * Get maximal length of cell
     * @return int
     */
    public function getLength()
    {
        return $this->maxlen;
    }

    /**
     * Set the type of cell
     * @param string type
     * @throw \InvalidArgumentException
     * @return Column
     */
    public function setType($type)
    {
        if (!in_array($type, array(self::RENDER_STRING, self::RENDER_EMAIL, self::RENDER_BOOL, self::RENDER_DATE, self::RENDER_ARRAY))) {
            throw new \InvalidArgumentException('Unknown cell type!');
        }

        $this->type = $type;
        return $this;
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
     * Set format of the cell
     * @param string format
     * @return Column
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Get the format
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Is sortable?
     * @return bool
     */
    public function isSortable()
    {
        return $this->sortable;
    }


    /**
     * Set sortable
     * @param bool sortable
     * @return Column
     */
    public function setSortable($sortable = TRUE)
    {
        $this->sortable = $sortable;
        return $this;
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
            if ($column === $this->columnName) {
                return $type;
            }
        }

        return NULL;
    }


    /**
     * Get date/time format
     * @return string
     */
    public function getDateTimeFormat()
    {
        return $this->dateTimeFormat;
    }


    /**
     * Set date/time format
     * @param string datetime format
     * @return Column
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->dateTimeFormat = $dateTimeFormat;
        return $this;
    }


    /**
     * Get grid
     * @return Grid
     */
    public function getGrid()
    {
        return $this->getParent()->getParent();
    }

    /**
     * Render boolean
     * @param bool value
     */
    public function renderBoolean($value)
    {
        $icon = $value ? 'check' : 'closethick';
        $el = Html::el('span');
        $el->data['value'] = $value ? '1' : '0';
        $el->data['type'] = 'bool';
        $el->add(Html::el("span class='ui-icon ui-icon-$icon'"));
        return $el;
    }


    /**
     * Render datetime
     * @param \Datetime|string value
     * @param string datetime|date format
     */
    public function renderDateTime($value, $format)
    {
        if ($value instanceof \DateTime) {
            return $value->format($format);
        } else {
            return date($format, (is_numeric($value) ? $value : strtotime($value)));
        }
    }

    /**
     * Render the text, takes care of length
     * @param string $text     text to render
     * @param int $maxlen maximum length of text
     */
    public function renderText($text, $maxlen)
    {
        if (is_null($maxlen) || Strings::length($text) < $maxlen) {
            return Html::el('span')->setText($text);
        } else {
            return Html::el('span')->title($text)
                ->setText(Strings::truncate($text, $maxlen));
        }
    }

    /**
     * Render the email address, takes care of length
     * @param string $email  email address
     * @param int $maxlen maximum length of text
     * @return mixed
     */
    public function renderEmail($email, $maxlen)
    {
        if (is_null($this->spamProtection)) {
            $href = $email;
            $text = htmlspecialchars($email, ENT_QUOTES);
        } else {
            $href = str_replace('@', $this->spamProtection, $email);
            $text = str_replace('@',
                '@<span style="display:none;">'
                    . $this->spamProtection
                    . '</span>',
                htmlspecialchars($email, ENT_QUOTES)
            );
        }
        $el = Html::el('a')->href('mailto:' . $href);
        if (is_null($maxlen) || Strings::length($email) < $maxlen) {
            return $el->setHtml($text);
        } else {
            return $el->title($href)
                ->setText(Strings::truncate($email, $maxlen));
        }
    }

    /**
     * Render email as list
     * @param $array
     * @param $maxlen
     * @return string
     */
    public function renderArray($array, $maxlen)
    {
        $text = implode($this->arrayDelimiter, (array)$array);

        if (is_null($maxlen) || Strings::length($text) < $maxlen) {
            return $text;
        } else {
            return Strings::truncate($text, $maxlen);
        }
    }

    /**
     * Default cell renderer
     * @param mixed $record
     * @param Column $column
     * @return mixed
     */
    public function defaultCellRenderer($value, $record, $column)
    {
        // boolean
        if ($this->type === self::RENDER_BOOL || is_bool($value)) {
            return $this->renderBoolean($value);

            // date
        } elseif ($this->type === self::RENDER_DATE || $value instanceof \DateTime) {
            return $this->renderDateTime($value, $this->dateTimeFormat);

            // email
        } elseif ($this->type === self::RENDER_EMAIL) {
            return $this->renderEmail($value, $this->maxlen);

            // array
        } elseif ($this->type === self::RENDER_ARRAY || is_array($value)) {
            return $this->renderArray($value, $this->maxlen);

            // string
        } else {
            if (!is_null($this->format)) {
                $value = $this->getGrid()->formatRecordString($record, $this->format);
            }
            return $this->renderText($value, $this->maxlen);
        }
    }


    /**
     * Render cell
     * @param mixed record
     */
    public function renderCell($record)
    {
        $value = $this->getColumnValue($record);

        $params = array($value, $record, $this);

        if ($this->rendererParamsNum < 3) { //slice the array
            $params = array_slice($params, 0, $this->rendererParamsNum);
        } elseif ($this->rendererParamsNum > 3) { //pad the array
            $params = array_pad($params, $this->rendererParamsNum, NULL);
        }

        $column = call_user_func_array($this->renderer ? : array($this, 'defaultCellRenderer'), $params);
        if (!($column instanceOf Html)) {
            $column = Html::el('span')->setText($column);
        }
        if ($this->editable) {
            $column->class[] = 'editable';
            if (!isset($column->data['value'])) {
                $column->data['value'] = $column->getText();
            }
            $column->data['url'] = $this->getGrid()->link('edit!');
            $column->data['name'] = $this->getName();
            $column->data['id'] = $this->getGrid()->getModel()->getUniqueId($record);
            $column->title = 'Click to edit';
        }
        echo $column;
    }

    /**
     * @param string $columnName
     * @return \Gridito\Column
     */
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;
        return $this;
    }


    /**
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Returns the value for this column from the row
     * @param $record mixed one row from which to get value
     * @return mixed
     */
    public function getColumnValue($record)
    {
        return $this->getGrid()->getModel()->getItemValue($record, $this->getColumnName());
    }
}
