<?php

namespace Gridito;
use Nette\Utils\Strings;
use Nette\Utils\Html;

/**
 * Grid column
 *
 * @author Jan Marek
 * @license MIT
 */
class Column extends \Nette\Application\UI\Control
{

    const STRING = ':string';
    const EMAIL = ':email';
    const BOOL = ':boolean';
    const DATE = ':date';

    /** @var string */
    private $label;

    /** @var callback */
    private $renderer = null;

    /** @var int */
    private $maxlen = null;

    /** @var string */
    private $type = self::STRING;

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
     * @return Column
     */
    public function setRenderer($cellRenderer)
    {
        $this->renderer = $cellRenderer;
        return $this;
    }

    /**
     * Set maximal length of cell
     * @param $maxlen
     * @return Column
     */
    public function setLength($maxlen)
    {
        $this->maxlen = $maxlen;
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
        if (!in_array($type, array(self::STRING, self::EMAIL, self::BOOL, self::DATE))) {
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
     * @param mixed format
     * @return Column
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Get the format
     * @return mixed
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

        list($column, $type) = $sorting;

        return $column === $this->columnName ? $type : NULL;
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
    public static function renderBoolean($value)
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
    public static function renderDateTime($value, $format)
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
     * @param int     $maxlen maximum length of text
     */
    public static function renderText($text, $maxlen)
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
     * @param int     $maxlen maximum length of text
     * @return mixed
     */
    public static function renderEmail($email, $maxlen)
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
     * Default cell renderer
     * @param mixed $record
     * @param Column $column
     * @return mixed
     */
    public function defaultCellRenderer($record, $column)
    {
        $value = $column->getColumnValue($record);

        // boolean
        if ($this->type === self::BOOL || is_bool($value)) {
            return self::renderBoolean($value);

            // date
        } elseif ($this->type === self::DATE || $value instanceof \DateTime) {
            return self::renderDateTime($value, $this->dateTimeFormat);

            // email
        } elseif ($this->type === self::EMAIL) {
            return self::renderEmail($value, $this->maxlen);

            // string
        } else {
            if (!is_null($this->format)) {
                $value = Grid::formatRecordString($record, $this->format);
            }
            return self::renderText($value, $this->maxlen);
        }
    }


    /**
     * Render cell
     * @param mixed record
     */
    public function renderCell($record)
    {
        $column = call_user_func($this->renderer ? : array($this, 'defaultCellRenderer'), $record, $this);
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
