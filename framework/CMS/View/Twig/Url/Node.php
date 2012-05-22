<?php

class CMS_View_Twig_Url_Node extends Twig_Node
{
    /**
     * Compiles the tag
     *
     * @param object $compiler
     * @return void
     */
    public function compile(Twig_Compiler $compiler)
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
            ->write('echo CMS_Mapper::urlFor(')
            ->subcompile($this->getNode('route'))
            ->write(', $route_params)')
            ->raw(";\n");
    }
}