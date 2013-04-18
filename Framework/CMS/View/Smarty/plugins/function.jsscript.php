<?php

function smarty_function_jsscript($params, &$smarty)
{
    $file = $params['file'];
    if ($params['theme']) {
        $file = '/themes/' . $params['theme'] . '/scripts/' . $file;
    }
    Scripts::add(SITE_DIR . $file);
}