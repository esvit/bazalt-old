<?php

class CMS_View_Twig_Extension extends Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'number' => new Twig_Filter_Function('twig_number_filter'),
            'truncate' => new Twig_Filter_Function('twig_truncate_filter'),
            'print_r' => new Twig_Filter_Function('twig_print_r_filter'),
            'instanceof' => new Twig_Filter_Function('twig_instanceof')
        );
    }

    /**
     * Returns the added token parsers
     *
     * @return array
     */
    public function getTokenParsers()
    {
        return array(
            new CMS_View_Twig_Url_TokenParser(),
            new CMS_View_Twig_JsLib_TokenParser(),
            new CMS_View_Twig_Tr_TokenParser(),
            new CMS_View_Twig_Include_TokenParser(),
            new CMS_View_Twig_Option_TokenParser(),
            new CMS_View_Twig_Widgets_TokenParser(),
            new CMS_View_Twig_Hook_TokenParser()
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bazalt_twig';
    }
}

function twig_number_filter($number, $decimals = 0, $dec_point = '.', $thousands_sep = ',')
{
    return number_format($number, $decimals, $dec_point, $thousands_sep);
}

function twig_truncate_filter($string, $size = 200)
{
    require_once dirname(__FILE__) . '/../PHP/helpers/truncate.php';
    return truncate($string, $size, true, true);
}

function twig_print_r_filter($obj)
{
    return print_r($obj, true);
}

function twig_instanceof($obj, $class)
{
    return ($obj instanceof $class);
}