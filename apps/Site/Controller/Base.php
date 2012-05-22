<?php

abstract class Site_Controller_Base extends CMS_Controller_Abstract
{
    protected $view;

    public function preAction($action, $args)
    {
        $this->view = CMS_Application::current()->View;
    }
}
