<?php

namespace Framework\CMS;

class Component
{
    protected $baseDir = null;

    protected $config = null;

    protected $view = null;

    public function config()
    {
        return $this->config;
    }

    public function __construct(Model\Component $component, $folder)
    {
        $this->baseDir = $folder;

        $this->config = $component;

        if (is_dir($folder . '/locale')) {
            //$this->hasLocale('locale'); // add locale folder
        }
    }

    public function view()
    {
        if ($this->view === null) {
            $this->view = View::root();
            $folders = $this->view->folders();
            $theme = array_pop($folders);
            $folders []= $this->baseDir . PATH_SEP . 'views';
            $folders []= $theme;
            $this->view->folders($folders);
        }
        return $this->view;
    }

    public function initComponent(Application $app)
    {

    }
}