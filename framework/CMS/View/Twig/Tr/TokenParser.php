<?php

class CMS_View_Twig_Tr_TokenParser extends Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $count = null;
        $plural = null;

        if (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $stream->expect(Twig_Token::BLOCK_END_TYPE);
            $body = $this->parser->subparse(array($this, 'decideForFork'));
            if ('plural' === $stream->next()->getValue()) {
                $count = new Twig_Node_Expression_Name($stream->expect(Twig_Token::NAME_TYPE)->getValue(), $lineno);
                $stream->expect(Twig_Token::BLOCK_END_TYPE);
                $plural = $this->parser->subparse(array($this, 'decideForEnd'), true);
            }
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $this->checkTransString($body, $lineno);

        return new CMS_View_Twig_Tr_Node($body, $plural, $count, $lineno, $this->getTag());
    }

    public function decideForFork($token)
    {
        return $token->test(array('plural', 'endtr'));
    }

    public function decideForEnd($token)
    {
        return $token->test('endtr');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @param string The tag name
     */
    public function getTag()
    {
        return 'tr';
    }

    protected function checkTransString(Twig_NodeInterface $body, $lineno)
    {
        foreach ($body as $i => $node) {
            if ($node instanceof Twig_Node_Text) {
                continue;
            }
            if ($node instanceof Twig_Node_Print) {
                foreach ($node as $n) {
                    if (!($n instanceof Twig_Node_Expression_Name)) {
                        throw new Twig_Error_Syntax(sprintf('The text to be translated with "trans" can only contain references to simple variables'), $lineno);
                    }
                }
            }
        }
    }
}