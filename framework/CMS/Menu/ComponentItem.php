<?php

abstract class CMS_Menu_ComponentItem extends CMS_Menu_Item
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

    public function __construct(CMS_Component $component, $element = null)
    {
        $this->component = $component;
        $this->view = $component->View;
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
