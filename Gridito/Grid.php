<?php

namespace Gridito;

use Nette\ComponentModel\Container, Nette\Utils\Paginator;
use Nette\Utils\Strings;

/**
 * Grid
 * @author Jan Marek
 * @license MIT
 */
class Grid extends \Nette\Application\UI\Control
{

    /** @var Model\IModel */
    private $model;

    /** @var \Nette\Utils\Paginator */
    private $paginator;

    /** @var \Nette\Http\Session */
    private $session;

    /** @var \Nette\Http\Request */
    private $request;

    /** @var int */
    private $defaultItemsPerPage = 20;

    /** @var string */
    public $defaultSortColumn = null;

    /** @var string */
    public $defaultSortType = null;

    /**
     * @var string
     * @persistent
     */
    public $sortColumn = null;

    /**
     * @var string
     * @persistent
     */
    public $sortType = null;

    /** @var string */
    private $ajaxClass = 'ajax';

    /** @var bool */
    private $highlightOrderedColumn = true;

    /** @var string|callable */
    private $rowClass = null;

    /** @var callable */
    private $editHandler = null;


    public function __construct(\Nette\Http\Session $session, \Nette\Http\Request $request, \VisualPaginator\VisualPaginator $paginator)
    {
        parent::__construct();

        $this->session = $session;
        $this->request = $request;

        $paginator->setPaginator(new \VisualPaginator\Paginator);

        $this->addComponent(new Container, 'toolbar');
        $this->addComponent(new Container, 'actions');
        $this->addComponent(new Container, 'columns');
        $this->addComponent($paginator, 'visualPaginator');

        $this['visualPaginator']->onChange[] = callback($this, 'invalidateControl');

        $this->paginator = $this['visualPaginator']->getPaginator();
        $this->paginator->setItemsPerPage($this->defaultItemsPerPage);
    }

    public static function formatRecordString($record, $formatString)
    {
        return Strings::replace($formatString, '#%[^%]*%#u',
            function ($m) use ($record)
            {
                $m = Strings::trim($m[0], '%');
                return $m != '' ? $record[$m] : '%';
            });
    }


    /**
     * @param bool highlight ordered column
     * @return Grid
     */
    public function setHighlightOrderedColumn($highlightOrderedColumn)
    {
        $this->highlightOrderedColumn = (bool)$highlightOrderedColumn;
        return $this;
    }


    /**
     * @return bool
     */
    public function getHighlightOrderedColumn()
    {
        return $this->highlightOrderedColumn;
    }


    /**
     * Is column highlighted?
     * @param Column $column
     * @return bool
     */
    public function isColumnHighlighted(Column $column)
    {
        $sorting = $this->getSorting();

        if (!$this->highlightOrderedColumn || $sorting === NULL) {
            return FALSE;
        }

        return $sorting[0] === $column->getColumnName();
    }

    public function setRowClass($class)
    {
        $this->rowClass = $class;
        return $this;
    }


    public function getRowClass($iterator, $row)
    {
        if (is_callable($this->rowClass)) {
            return call_user_func($this->rowClass, $iterator, $row);
        } elseif (is_string($this->rowClass)) {
            return $this->rowClass;
        } else {
            return null;
        }
    }


    /**
     * Get model
     * @return Model\IModel
     */
    public function getModel()
    {
        if ($this->model === NULL) {
            throw new \InvalidArgumentException('The model was not set! Use \'setModel\' to set model first!');
        }

        return $this->model;
    }


    /**
     * Set model
     * @param Model\IModel model
     * @return Grid
     */
    public function setModel(Models\IModel $model)
    {
        $this->getPaginator()->setItemCount($model->count());
        $this->model = $model;
        return $this;
    }


    /**
     * Get items per page
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->getPaginator()->getItemsPerPage();
        return $this;
    }


    /**
     * Set items per page
     * @param int items per page
     * @return Grid
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->getPaginator()->setItemsPerPage($itemsPerPage);
        return $this;
    }


    /**
     * Get ajax class
     * @return string
     */
    public function getAjaxClass()
    {
        return $this->ajaxClass;
    }


    /**
     * Set ajax class
     * @param string ajax class
     * @return Grid
     */
    public function setAjaxClass($ajaxClass)
    {
        $this->ajaxClass = $ajaxClass;
        return $this;
    }

    /**
     * Set default sorting
     * @param string $column column name for model
     * @param string $type asc or desc
     * @return Grid
     */
    public function setDefaultSorting($column, $type)
    {
        $this->defaultSortColumn = $column;
        $this->defaultSortType = $type;
        return $this;
    }

    /**
     * Get sorting options
     * @return array|null array with sorting column for model and asc or desc
     */
    public function getSorting()
    {
        $columns = $this['columns'];
        /* @var $columns \Nette\ComponentModel\IContainer */

        $sortByColumn = $this->sortColumn ? $columns->getComponent($this->sortColumn) : NULL;
        /* @var $sortByColumn \Gridito\Column */

        if ($sortByColumn && $sortByColumn->isSortable() && ($this->sortType === IModel::ASC || $this->sortType === IModel::DESC)) {
            return array($sortByColumn->getColumnName(), $this->sortType);
        } elseif ($this->defaultSortColumn) {
            return array($this->defaultSortColumn, $this->defaultSortType);
        } else {
            return NULL;
        }
    }

    /**
     * Get paginator
     * @return \Nette\Utils\Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }


    /**
     * Get security token
     * @return string
     */
    public function getSecurityToken()
    {
        $session = $this->session->getSection(__CLASS__ . '-' . __METHOD__);

        if (empty($session->securityToken)) {
            $session->securityToken = md5(uniqid(mt_rand(), true));
        }

        return $session->securityToken;
    }


    /**
     * Has toolbar
     * @return bool
     */
    public function hasToolbar()
    {
        return count($this['toolbar']->getComponents()) > 0;
    }


    /**
     * Has actions
     * @return bool
     */
    public function hasActions()
    {
        return count($this['actions']->getComponents()) > 0;
    }


    /**
     * Set edit handler
     * @param callback handler
     * @return Grid
     */
    public function setEditHandler($callback)
    {
        $this->editHandler = $callback;
        return $this;
    }

    /**
     * Handle edit
     */
    public function handleEdit()
    {
        if ($this->presenter->isAjax()) {
            $post = $this->request->getPost();
            foreach ($post as $column => $value) {
                if ($column == 'id' ||
                    $this['columns']->getComponent($column)->isEditable()
                ) {
                    continue;
                }
                throw new \Nette\Application\ForbiddenRequestException('Column \'' . $column . '\' is not editable');
            }
            call_user_func($this->editHandler, $post);
        }
    }

    /**
     * Sorting signal
     * @param $sortColumn
     * @param $sortType
     */
    public function handleSort($sortColumn, $sortType)
    {
        if ($this->presenter->isAjax()) {
            $this->invalidateControl();
        }
        $this->paginator->page = 1;
    }


    /**
     * Create template
     * @return Template
     */
    protected function createTemplate($class = null)
    {
        return parent::createTemplate($class)->setFile(__DIR__ . '/templates/grid.phtml');
    }


    /**
     * Render grid
     */
    public function render()
    {
        $this->model->setLimit($this->paginator->getLength());
        $this->model->setOffset($this->paginator->getOffset());

        if ($this->sortColumn && $this['columns']->getComponent($this->sortColumn)->isSortable()) {
            $sortByColumn = $this['columns']->getComponent($this->sortColumn);
            $this->model->setSorting($sortByColumn->getColumnName(), $this->sortType);
        } elseif ($this->defaultSortColumn) {
            $this->model->setSorting($this->defaultSortColumn, $this->defaultSortType);
        }
        $this['visualPaginator']->setClass(array('paginator', $this->ajaxClass));

        $this->template->render();
    }


    /**
     * Add column
     * @param string name
     * @param string label
     * @param array options
     * @return Column
     */
    public function addColumn($name, $label = null, array $options = array())
    {
        $componentName = \Nette\Utils\Strings::webalize($name);
        $componentName = strtr($componentName, '-', '_');
        $column = new Column($this['columns'], $componentName);
        $column->setColumnName($name);
        $column->setLabel($label);
        $this->setOptions($column, $options);
        return $column;
    }


    /**
     * Add action button
     * @param string button name
     * @param string label
     * @param array options
     * @return Button
     */
    public function addButton($name, $label = null, array $options = array())
    {
        $button = new Button($this['actions'], $name);
        $button->setLabel($label);
        $this->setOptions($button, $options);
        return $button;
    }

    /**
     * Add check button
     * @param string button name
     * @param string label
     * @param array options
     * @return CheckButton
     */
    public function addCheckButton($name, $label = null, array $options = array())
    {
        $button = new CheckButton($this['actions'], $name);
        $button->setLabel($label);
        $this->setOptions($button, $options);
        return $button;
    }


    /**
     * Add window button
     * @param string button name
     * @param string label
     * @param array options
     * @return WindowButton
     */
    public function addWindowButton($name, $label = null, array $options = array())
    {
        $button = new WindowButton($this['actions'], $name);
        $button->setLabel($label);
        $this->setOptions($button, $options);
        return $button;
    }


    /**
     * Add action button to toolbar
     * @param string button name
     * @param string label
     * @param array options
     * @return Button
     */
    public function addToolbarButton($name, $label = null, array $options = array())
    {
        $button = new Button($this['toolbar'], $name);
        $button->setLabel($label);
        $this->setOptions($button, $options);
        return $button;
    }


    /**
     * Add window button to toolbar
     * @param string button name
     * @param string label
     * @param array options
     * @return WindowButton
     */
    public function addToolbarWindowButton($name, $label = null, array $options = array())
    {
        $button = new WindowButton($this['toolbar'], $name);
        $button->setLabel($label);
        $this->setOptions($button, $options);
        return $button;
    }


    /**
     * Set page
     * @param int page
     */
    private function setPage($page)
    {
        $this->getPaginator()->setPage($page);
    }

    /**
     * Helper for set<option> methods
     * @param $object
     * @param $options
     * @throws \InvalidArgumentException
     */
    protected function setOptions($object, $options)
    {
        foreach ($options as $option => $value) {
            $method = 'set' . ucfirst($option);
            if (method_exists($object, $method)) {
                $object->$method($value);
            } else {
                throw new \InvalidArgumentException('Option with name \'' . $option . '\' does not exist.');
            }
        }
    }

}
