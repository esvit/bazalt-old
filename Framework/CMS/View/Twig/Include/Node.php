<?php

class CMS_View_Twig_Include_Node extends Twig_Node
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

        // Output the route
        $compiler
            ->write('$view = (isset($context["_view"]) ? $context["_view"] : \Framework\CMS\Application::current()->view());')
            ->raw("\n")
            ->write('$view->display(')
            ->subcompile($this->getNode('file'))
            ->write(', array_merge($context, $params))')
            ->raw(";\n");
    }
}