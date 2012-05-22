<?php

function smarty_function_cmsoption($params, &$smarty)
{
    $name = $params['name'];
    $val = CMS_Option::get($name);
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $val);
    } else {
        return $val;
    }
}