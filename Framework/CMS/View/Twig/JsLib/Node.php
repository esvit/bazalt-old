<?php

class CMS_View_Twig_JsLib_Node extends Twig_Node
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

        $compiler
            ->write('echo Assets_JS::addPackage(')
            ->subcompile($this->getNode('name'))
            ->write(')')
            ->raw(";\n");
    }
}