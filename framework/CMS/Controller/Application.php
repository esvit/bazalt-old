<?php

abstract class CMS_Controller_Application extends CMS_Controller_Abstract
{
    protected $application;

    public function preAction($action, $args)
    {
        parent::preAction($action, $args);

        $this->application = CMS_Application::current();
    }
}