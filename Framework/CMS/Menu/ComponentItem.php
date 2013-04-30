<?php

namespace Framework\CMS\Menu;

use Framework\CMS as CMS;

abstract class ComponentItem extends Item
{
    protected $component = null;

    protected $view = null;

    protected $element = null;

    abstract function getItemType();

    /**
     * Menu settings
     *
     * @return string Settings template
     */
    abstract function getSettingsForm();

    public function __construct(CMS\Component $component, $element = null)
    {
        $this->component = $component;
        $this->view = $component->view();
        $this->element = $element;

        if ($element) {
            $this->title($element->title);
            $this->description($element->description);
        }
    }

    public function component()
    {
        return $this->component;
    }

    /**
     * Call before output item
     */
    public function prepare()
    {
    }
}