<?php

abstract class CMS_Controller_Abstract extends Routing_AbstractController
{
    protected $view;

    public function preAction($action, $args)
    {
        $this->view = CMS_Application::current()->View;
    }
}