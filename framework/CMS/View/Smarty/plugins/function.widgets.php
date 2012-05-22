<?php

function smarty_function_widgets($params, &$template)
{
    $position = $params['position'];
    if (array_key_exists('file', $params)) {
        $currentTemplate = $params['file'];
    } else {
        $currentTemplate = $template->getTemplateVars('currentTemplate');
    }

    echo CMS_Widget::getPosition(trim($currentTemplate), $position);
}