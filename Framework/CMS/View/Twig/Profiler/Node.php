<?php

class CMS_View_Twig_Profiler_Node extends Twig_Node
{
    public function __construct($name, $template, Twig_NodeInterface $body, $lineno, $tag = null)
    {
        parent::__construct(array('name' => $name, 'template' => $template, 'body' => $body), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->raw('$token = Logger::start("Twig_Template ' . $this->getNode('template') . '",')
            ->subcompile($this->getNode('name'))
            ->write(');')
            ->subcompile($this->getNode('body'))
            ->write('Logger::stop($token);')
        ;
    }
}