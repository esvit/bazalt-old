<?php

function smarty_function_css($params, &$smarty)
{
    $file = $params['file'];
    if (isset($params['theme'])) {
        $file = '/themes/' . $params['theme'] . '/styles/' . $file;
    }
    if (isset($params['component'])) {
        $component = $params['component'];
        $component->addStyle($file);
    } else {
        Assets_CSS::getInstance()->add(SITE_DIR . $file);
    }
}