<?php

namespace Gridito;

use Nette\Application\Responses\TextResponse;

/**
 * Window button
 *
 * @author Jan Marek
 * @license MIT
 */
class WindowButton extends BaseButton
{
    /** @var int|string */
    private $windowWidth = 600;

    /** @var string|int */
    private $windowHeight = "auto";

    /** @var int */
    private $windowModal = 1;

    /** @var int */
    private $windowDraggable = 1;

    /** @var int */
    private $windowResizable = 1;

    /**
     * Handle click signal
     * @param string security token
     * @param mixed primary key
     */
    public function handleClick($token, $uniqueId = null)
    {
        ob_start();
        parent::handleClick($token, $uniqueId);
        $output = ob_get_clean();

        if ($this->getPresenter()->isAjax()) {
            $this->getPresenter()->sendResponse(new TextResponse($output));
        } else {
            $this->getGrid()->getTemplate()->windowLabel = $this->getLabel();
            $this->getGrid()->getTemplate()->windowOutput = $output;
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
        $el->class[] = 'gridito-window-button';
        $el->data('gridito-window-title', $this->getLabel());
        $el->data('gridito-window-width', $this->windowWidth);
        $el->data('gridito-window-height', $this->windowHeight);
        $el->data('gridito-window-modal', $this->windowModal);
        $el->data('gridito-window-draggable', $this->windowDraggable);
        $el->data('gridito-window-resizable', $this->windowResizable);
        return $el;
    }

    /**
     * @param bool $windowDraggable
     * @return WindowButton
     */
    public function setWindowDraggable($windowDraggable = TRUE)
    {
        $this->windowDraggable = $windowDraggable ? 1 : 0;
        return $this;
    }

    /**
     * @return int
     */
    public function getWindowDraggable()
    {
        return $this->windowDraggable;
    }

    /**
     * @param bool $windowModal
     * @return WindowButton
     */
    public function setWindowModal($windowModal = TRUE)
    {
        $this->windowModal = $windowModal ? 1 : 0;
        return $this;
    }

    /**
     * @return int
     */
    public function getWindowModal()
    {
        return $this->windowModal;
    }

    /**
     * @param bool $windowResizable
     * @return WindowButton
     */
    public function setWindowResizable($windowResizable = TRUE)
    {
        $this->windowResizable = $windowResizable ? 1 : 0;
        return $this;
    }

    /**
     * @return int
     */
    public function getWindowResizable()
    {
        return $this->windowResizable;
    }

    /**
     * @param string|int $windowWidth
     * @return WindowButton
     */
    public function setWindowWidth($windowWidth = "auto")
    {
        $this->windowWidth = ($windowWidth === "auto" ? : intval($windowWidth));
        return $this;
    }

    /**
     * @return int|string
     */
    public function getWindowWidth()
    {
        return $this->windowWidth;
    }

    /**
     * @param string|int $windowHeight
     * @return WindowButton
     */
    public function setWindowHeight($windowHeight = "auto")
    {
        $this->windowHeight = ($windowHeight === "auto" ? : intval($windowHeight));
        return $this;
    }

    /**
     * @return string|int
     */
    public function getWindowHeight()
    {
        return $this->windowHeight;
    }

}