<?php

function smarty_function_url($params, &$template)
{
    if (empty($params['for'])) {
        throw new Exception('Function url must have parameter for');
    }
    $for = $params['for'];
    unset($params['for']);

    $component = $template->getTemplateVars('component');

    if ($component) {
        $params['component'] = strToLower(get_class($component));
    }
    
    $url = CMS_Mapper::urlFor($for, $params);
    
    if(isset($params['assign'])) {
        $template->assign($params['assign'], $url);
    } else {
        return CMS_Mapper::urlFor($for, $params);
    }
}