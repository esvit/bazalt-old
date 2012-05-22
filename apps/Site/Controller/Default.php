<?php

class Site_Controller_Default extends Site_Controller_Base
{
    public function defaultAction()
    {
        $this->view->display('index');
    }
}