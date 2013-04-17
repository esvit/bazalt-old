<?php

class CMS_View_Twig_MasterLayout_Node extends Twig_Node
{
    /**
     * Compiles the tag
     *
     * @param object $compiler
     * @return void
     */
    public function compile(Twig_Compiler $compiler)
    {
        // Output the route
        $compiler
            ->write('CMS_View::setLayout(')
            ->subcompile($this->getNode('template'))
            ->write(')')
            ->raw(";\n");
    }
}