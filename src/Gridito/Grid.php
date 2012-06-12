<?php

namespace Gridito;

use Nette\ComponentModel\Container, Nette\Utils\Paginator;
use Nette\Utils\Strings;

/**
 * Grid
 * @author Jan Marek
 * @author Natrim
 * @license MIT
 *
 * @property $rememberState bool
 * @property $highlightOrderedColumn bool
 * @property $rowClass callable|string|array
 * @property $model IModel
 * @property $itemsPerPage int
 * @property $ajaxClass string
 * @property $paginator \Nette\Utils\Paginator
 * @property $page int
 * @property $stateTimeout string|int
 *
 * @property-write $editHandler callable
 *
 * @property-read $sorting array|null
 * @property-read $securityToken
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

    /** @var array */
    private $defaultSorting = array();

    /** @var array */
    public $sorting = array();

    /** @var string */
    private $ajaxClass = 'ajax';

    /** @var bool */
    private $highlightOrderedColumn = true;

    /** @var string|callable */
    private $rowClass = null;

    /** @var callable */
    private $editHandler = null;

    /** @var bool save state into session? */
    private $remember = FALSE;

    /** @var int|string session timeout (default: until is browser closed) */
    private $timeout = 0;

    /**
     * Constructor
     * @param \Nette\Http\Session $session
     * @param \Nette\Http\Request $request
     */
    public function __construct(\Nette\Http\Session $session, \Nette\Http\Request $request)
    {
        parent::__construct();

        $this->session = $session;
        $this->request = $request;

        $this->addComponent(new Container, 'toolbar');
        $this->addComponent(new Container, 'actions');
        $this->addComponent(new Container, 'columns');

        //VisualPaginator
        if (class_exists('VisualPaginator\VisualPaginator')) {
            $this->addComponent(new \VisualPaginator\VisualPaginator, 'visualPaginator');
        }
    }

    /**
     * Returns array of classes persistent parameters. They have public visibility and are non-static.
     * @return array
     */
    public static function getPersistentParams()
    {
        return array('sorting');
    }


    /**
     * Save the state to session?
     * @param bool $remember
     * @return Grid
     */
    public function setRememberState($remember = TRUE)
    {
        $this->remember = (bool)$remember;

        $vp = $this->getComponent('visualPaginator', FALSE);
        if ($vp) {
            $vp->setRememberState($this->remember);
            if ($vp->rememberState) {
                $vp->setSession($this->session);
            }
        }

        return $this;
    }

    /**
     * Is the state saving in session
     * @return bool
     */
    public function getRememberState()
    {
        return $this->remember;
    }

    /**
     * Loads state informations.
     * @param  array
     * @return void
     */
    public function loadState(array $params)
    {
        if ($this->rememberState) {
            $session = $this->getStateSession();
            foreach ($this->getPersistentParams() as $name) {
                if (isset($session[$name]) && !isset($params[$name])) {
                    $params[$name] = $session[$name];
                }
            }
        }

        parent::loadState($params);
    }

    /**
     * Saves state informations for next request.
     * @param  array
     * @param  PresenterComponentReflection (internal, used by Presenter)
     * @return void
     */
    public function saveState(array & $params, $reflection = NULL)
    {
        parent::saveState($params, $reflection);

        if ($this->rememberState) {
            $session = $this->getStateSession();

            foreach ($this->getPersistentParams() as $name) {
                $session[$name] = $this->{$name};
            }

            $session->setExpiration($this->timeout);
        }
    }


    /**
     * @return \Nette\Http\SessionSection
     */
    protected function getStateSession()
    {
        return $this->session->getSection('Gridito/' . $this->lookupPath('Nette\ComponentModel\IComponent', FALSE) ? : $this->getName() . '/states');
    }

    /**
     * Format the string by defined format
     * @param $record
     * @param $formatString
     * @return mixed
     */
    public function formatRecordString($record, $formatString)
    {
        return Strings::replace($formatString, '#%[^%]*%#u',
            function ($m) use ($record)
            {
                $m = Strings::trim($m[0], '%');
                return $m !== '' ? $this->getModel()->getItemValue($record, $m) : '%';
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

        return isset($sorting[$column->getColumnName()]);
    }

    /**
     * Sets row class
     * @param $class
     * @return Grid
     */
    public function setRowClass($class)
    {
        $this->rowClass = $class;
        return $this;
    }

    /**
     * Returns row class
     * @param $iterator
     * @param $row
     * @return callable|mixed|null|string
     */
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
    public function setModel(Model\IModel $model)
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
    public function setDefaultSorting($column, $type = Model\IModel::ASC)
    {
        if (is_array($column)) {
            $this->defaultSorting = array_map(function($type)
            {
                return ((is_string($type) && strncasecmp($type, 'd', 1)) || $type > 0 ? Model\IModel::ASC : Model\IModel::DESC);
            }, $column);
        } else {
            $this->defaultSorting[$column] = ((is_string($type) && strncasecmp($type, 'd', 1)) || $type > 0 ? Model\IModel::ASC : Model\IModel::DESC);
        }

        return $this;
    }

    /**
     * Get sorting options
     * @return array|null array with sorting column for model and asc or desc
     */
    public function getSorting()
    {
        if (is_array($this->sorting) && count($this->sorting) > 0) {
            /* @var $columns \Nette\ComponentModel\IContainer */
            $columns = $this['columns'];

            $sorting = array();

            foreach ($this->sorting as $sortColumn => $sortType) {
                if (is_null($sortType)) {
                    continue;
                }

                /* @var $sortByColumn \Gridito\Column */
                $sortByColumn = $sortColumn ? $columns->getComponent($sortColumn) : NULL;

                if ($sortByColumn && $sortByColumn->isSortable()) {
                    $sorting[$sortByColumn->getColumnName()] = ((is_string($sortType) && strncasecmp($sortType, 'd', 1)) || $sortType > 0 ? Model\IModel::ASC : Model\IModel::DESC);
                }
            }

            if (count($sorting) > 0) {
                return $sorting;
            } else {
                return NULL;
            }

        } elseif (is_array($this->defaultSorting) && count($this->defaultSorting) > 0) {
            return $this->defaultSorting;
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
        if (is_null($this->paginator)) {
            if (is_null($vp = $this->getComponent('visualPaginator', FALSE))) {
                throw new \Nette\InvalidStateException('Paginator was not defined! Use \'setPaginator\' to set paginator or add \'VisualPaginator\' component to project!');
            } else {
                $this->setPaginator($vp->getPaginator());
            }
        }

        return $this->paginator;
    }

    /**
     * Sets paginator
     * @param \Nette\Utils\Paginator $paginator
     * @return Grid
     */
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;

        $this->paginator->setItemsPerPage($this->defaultItemsPerPage); //sets default values

        return $this;
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
                if ($column === $this->getModel()->getPrimaryKey() || $this['columns']->getComponent($column)->isEditable()) {
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
    public function handleSort($column, $type)
    {
        //set sorting
        $this->sorting[$column] = $type;

        if ($this->presenter->isAjax()) {
            $this->invalidateControl();
        }
    }


    /**
     * Create template
     * @return \Nette\Templating\Template
     */
    protected function createTemplate($class = null)
    {
        return parent::createTemplate($class)->setFile(__DIR__ . '/../../templates/grid.phtml');
    }


    /**
     * Render grid
     */
    public function render()
    {
        $this->getModel()->setLimit($this->paginator->getLength());
        $this->getModel()->setOffset($this->paginator->getOffset());

        $sorting = $this->getSorting();
        if ($sorting) {
            $this->getModel()->setSorting($sorting);
        }

        if (!is_null($vp = $this->getComponent('visualPaginator', FALSE))) {
            $vp->setClass(array('paginator', $this->ajaxClass));
            $this->template->paginator = $vp;
        } else {
            $this->template->paginator = $this->getPaginator();
        }

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
     * Add virtual column
     * @param string name
     * @param string label
     * @param mixed|callable data generator
     * @param array options
     * @return VirtualColumn
     */
    public function addVirtualColumn($name, $label = null, $dataGenerator = null, array $options = array())
    {
        $componentName = \Nette\Utils\Strings::webalize($name);
        $componentName = strtr($componentName, '-', '_');
        $column = new VirtualColumn($this['columns'], $componentName);
        $column->setColumnName($name);
        $column->setLabel($label);
        $column->setDataGenerator($dataGenerator);
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
     * @param string $name button name
     * @param string $label
     * @param array $options
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
     * Set page helper
     * @param int page
     * @return Grid
     */
    private function setPage($page)
    {
        $this->getPaginator()->setPage($page);
        return $this;
    }

    /**
     * Get page helper
     * @param int page
     */
    private function getPage()
    {
        return $this->getPaginator()->getPage();
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

    /**
     * Sets the timeout for the saved state in session
     * @param int|string $timeout
     * @return Grid
     */
    public function setStateTimeout($timeout)
    {
        $this->timeout = $timeout;

        $vp = $this->getComponent('visualPaginator', FALSE);
        if ($vp) {
            $vp->setStateTimeout($this->timeout);
        }

        return $this;
    }

    /**
     * @return int|string
     */
    public function getStateTimeout()
    {
        return $this->timeout;
    }
}
