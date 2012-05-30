<?php

namespace Gridito;

/**
 * Action button
 *
 * @author Jan Marek
 * @author Natrim
 * @license MIT
 *
 * @property $checked bool
 * @property $ajax bool
 */
class CheckButton extends BaseButton
{
    /** @var bool */
    private $ajax = false;

    /** @var bool|callable */
    private $checked = false;

    /**
     * Set checked
     * @param bool|callable $checked
     * @return CheckButton
     */
    public function setChecked($checked = true)
    {
        $this->checked = $checked;
        return $this;
    }


    /**
     * Is button checked
     * @param mixed $row
     * @return bool
     */
    public function isChecked($row = null)
    {
        return is_bool($this->checked) ? $this->checked : call_user_func($this->checked, $row);
    }


    /**
     * Is ajax?
     * @return bool
     */
    public function isAjax()
    {
        return $this->ajax;
    }


    /**
     * Set ajax mode
     * @param bool ajax
     * @return Button
     */
    public function setAjax($ajax)
    {
        $this->ajax = (bool)$ajax;
        return $this;
    }


    /**
     * Handle click signal
     * @param string security token
     * @param mixed primary key
     */
    public function handleClick($token, $uniqueId = null)
    {
        parent::handleClick($token, $uniqueId);

        if ($this->getPresenter()->isAjax()) {
            $this->getGrid()->invalidateControl();
        } else {
            $this->getGrid()->redirect('this');
        }
    }


    /**
     * Create button element
     * @param mixed $row
     * @return \Nette\Utils\Html
     */
    public function createButton($row = null)
    {
        $el = parent::createButton($row);
        if ($this->isChecked($row)) {
            $el->class[] = 'checked';
        }
        $el->class[] = $this->isAjax() ? $this->getGrid()->getAjaxClass() : null;
        return $el;
    }

}
