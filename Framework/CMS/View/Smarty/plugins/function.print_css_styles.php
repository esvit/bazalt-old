<?php

function smarty_function_print_css_styles($params, &$smarty)
{
    return Styles::getHtml();
}