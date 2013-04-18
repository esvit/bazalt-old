<?php

class CMS_View_Twig_MetaData_Node extends Twig_Node
{
    public function __construct(Twig_NodeInterface $body, $filename, $lineno, $tag = 'metadata')
    {
        parent::__construct(array('body' => $body), array('filename' => $filename), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write('\Framework\CMS\View::$headAppendString .= "\n" . ((STAGE == DEVELOPMENT_STAGE) ? ("<!-- ' . $this->getAttribute('filename') . ' ' . $this->getLine() . ' -->\n") : "") . ob_get_clean() . "\n";' . "\n")
        ;
    }
}