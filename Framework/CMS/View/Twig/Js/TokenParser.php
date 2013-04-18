<?php

class CMS_View_Twig_Js_TokenParser extends Twig_TokenParser
{
    /**
     * @param Twig_Token $token
     * @return object
     */
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();

        $name = $this->parser->getExpressionParser()->parseExpression();

        $args = ['name' => $name];
        if ($this->parser->getStream()->test(Twig_Token::PUNCTUATION_TYPE, ',')) {
            $this->parser->getStream()->expect(Twig_Token::PUNCTUATION_TYPE, ',');
            $args['params'] = $this->parser->getExpressionParser()->parseExpression();
        }
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
        $env = $this->parser->getEnvironment();
        $paths = $env->getLoader()->getPaths();

        return new CMS_View_Twig_Js_Node($args, $paths[0], $lineno, $this->getTag());
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return 'js';
    }
}