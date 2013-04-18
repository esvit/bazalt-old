<?php

namespace Framework\CMS\View\Twig;

use Framework\CMS as CMS;
use Framework\Core\Helper as Helper;

class Extension extends \Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'number' => new \Twig_Filter_Function('twig_number_filter'),
            'truncate' => new \Twig_Filter_Function('twig_truncate_filter'),
            'thumb' => new \Twig_Filter_Function('thumb'),
            'print_r' => new \Twig_Filter_Function('twig_print_r_filter'),
            'format_date' => new \Twig_Filter_Function('twig_format_date_filter'),
            'format_time' => new \Twig_Filter_Function('twig_format_time_filter'),
            'class_name' => new \Twig_Filter_Function('twig_class_name_filter'),
            'sortby' => new \Twig_Filter_Function('twig_sortby_filter'),
            'nofollow' => new \Twig_Filter_Function('twig_nofollow_filter'),
            'instanceof' => new \Twig_Filter_Function('twig_instanceof')
        );
    }

    public function getFunctions()
    {
        return array(
            'hasRight' => new \Twig_Function_Method($this, 'hasRight')
        );
    }

    public function hasRight($right, $component = null)
    {
        return CMS\User::get()->hasRight($component, $right);
    }

    /**
     * Returns the added token parsers
     *
     * @return array
     */
    public function getTokenParsers()
    {
        using('Framework.CMS');
        return array(
            new \CMS_View_Twig_Include_TokenParser(),
            new Url\TokenParser(),
            new \CMS_View_Twig_Widgets_TokenParser(),
            new \CMS_View_Twig_MetaData_TokenParser(),
            new \CMS_View_Twig_Js_TokenParser(),
            new \CMS_View_Twig_Tr_TokenParser()
            /*new \CMS_View_Twig_JsLib_TokenParser(),
            new \CMS_View_Twig_Option_TokenParser(),
            new \CMS_View_Twig_Widget_TokenParser(),
            new \CMS_View_Twig_MasterLayout_TokenParser(),
            new \CMS_View_Twig_Css_TokenParser(),
            new \CMS_View_Twig_Profiler_TokenParser(),
            new \CMS_View_Twig_Hook_TokenParser()*/
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