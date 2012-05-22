<?php

abstract class CMS_Component_Controller extends CMS_Controller_Abstract
{
    protected $component = null;

    protected $view = null;

    public function __construct($component)
    {
        $this->component = $component;

        $this->view = $component->View;
        if (!$this->view) {
            throw new Exception('Invalid view');
        }
        $this->view->assign('component', $component);
    }

    public function preAction($action, $args)
    {
    }
}