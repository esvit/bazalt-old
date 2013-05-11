<?php

namespace Framework\CMS;

abstract class AbstractController
{
    /**
     * @var View
     */
    protected $view = null;

    public function view()
    {
        return $this->view;
    }

    public function preAction($action, &$args)
    {
        $base = View::root();
        if (isset($args['component'])) {
            $component = Bazalt::getComponent($args['component']);
            unset($args['component']);
            $base = $component->view();
        }
        $this->breadcrumb = Breadcrumb::root();
        $this->view = $base->newScope();
    }
}