<?php

class CMS_View_Twig_MasterLayout_TokenParser extends Twig_TokenParser
{
    /**
     * @param Twig_Token $token
     * @return object
     */
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();

        $template = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return new CMS_View_Twig_MasterLayout_Node(array('template' => $template), array(), $lineno, $this->getTag());
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return 'masterlayout';
    }
}