<?php

class CMS_View_Twig_Option_Node extends Twig_Node
{
    /**
     * Compiles the tag
     *
     * @param object $compiler
     * @return void
     */
    public function compile(Twig_Compiler $compiler)
    {
        /*if ($this->hasNode('params')) {
            $compiler
                ->write('$route_params = ')
                ->subcompile($this->getNode('params'))
                ->raw(";\n");
        } else {
            $compiler
                ->write('$route_params = array()')
                ->raw(";\n");
        }*/

        // Output the route
        $compiler
            ->write('echo CMS_Option::get(')
            ->subcompile($this->getNode('name'))
            ->write(')')
            ->raw(";\n");
    }
}