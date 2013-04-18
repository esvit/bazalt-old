<?php

class CMS_View_Twig_Hook_Node extends Twig_Node
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
            ->write('Event::trigger("Hooks", ')
            ->subcompile($this->getNode('name'))
            ->write(', array($params))')
            ->raw(";\n")
            ->write('echo "<!--" .')
            ->subcompile($this->getNode('name'))
            ->write('. "-->"')
            ->raw(";\n");
    }
}