<?php

namespace Framework\CMS\View\Twig\Url;

class TokenParser extends \Twig_TokenParser
{
    /**
     * @param Twig_Token $token
     * @return object
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();

        // Find the route we're matching
        $route = $this->parser->getExpressionParser()->parseExpression();

        $args = ['route' => $route];
        // Check for arguments for the route
        if ($this->parser->getStream()->test(\Twig_Token::PUNCTUATION_TYPE, ',')) {
            $this->parser->getStream()->expect(\Twig_Token::PUNCTUATION_TYPE, ',');
            $args['params'] = $this->parser->getExpressionParser()->parseExpression();
        }

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new Node($args, array(), $lineno, $this->getTag());
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return 'url';
    }
}