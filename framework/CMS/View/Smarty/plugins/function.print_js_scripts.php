<?php

function smarty_function_print_js_scripts($params, &$smarty)
{
    return Scripts::getHtml();
}