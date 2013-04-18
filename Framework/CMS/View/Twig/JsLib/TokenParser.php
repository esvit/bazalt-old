<?php

class CMS_View_Twig_JsLib_TokenParser extends Twig_TokenParser
{
    /**
     * @param Twig_Token $token
     * @return object
     */
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();

        $name = $this->parser->getExpressionParser()->parseExpression();

        if ($this->parser->getStream()->test(Twig_Token::PUNCTUATION_TYPE, ',')) {
            $this->parser->getStream()->expect(Twig_Token::PUNCTUATION_TYPE, ',');
            $params = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $params = FALSE;
        }

        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return new CMS_View_Twig_JsLib_Node(array('name' => $name, 'params' => $params), array(), $lineno, $this->getTag());
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return 'jslib';
    }
}