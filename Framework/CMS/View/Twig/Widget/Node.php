<?php

class CMS_View_Twig_Widget_Node extends Twig_Node
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
                ->write('$params = ')
                ->subcompile($this->getNode('params'))
                ->raw(";\n");
        } else {
            $compiler
                ->write('$params = array()')
                ->raw(";\n");
        }

        $compiler
            ->write('echo CMS_Widget::getPosition(trim($this->getTemplateName()), trim(')
            ->subcompile($this->getNode('position'))
            ->write('))')
            ->raw(";\n");
    }
}