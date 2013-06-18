<?php

namespace Framework\CMS\View\Twig\Url;

class Node extends \Twig_Node
{
    /**
     * Compiles the tag
     *
     * @param object $compiler
     * @return void
     */
    public function compile(\Twig_Compiler $compiler)
    {
        if ($this->hasNode('params')) {
            $compiler
                ->write('$route_params = ')
                ->subcompile($this->getNode('params'))
                ->raw(";\n");
        } else {
            $compiler
                ->write('$route_params = array()')
                ->raw(";\n");
        }

        // Output the route
        $compiler
            ->write('echo \Bazalt\Routing\Route::urlFor(')
            ->subcompile($this->getNode('route'))
            ->write(', $route_params, isset($route_params["host"]) ? $route_params["host"] : false)')
            ->raw(";\n");
    }
}