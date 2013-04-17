<?php

function smarty_function_template($params, &$template)
{
    $name = $params['title'];
    unset($params['title']);

    if (array_key_exists('file', $params)) {
        $file = $params['file'];
        unset($params['file']);
    }

    if (empty($file)) {
        $template->assign('currentTemplate', $template->getTemplateResource());
    } else {
        $template->assign('currentTemplate', $file);
    }
    $template->assign('currentTemplateName', $name);

    Event::trigger('Hooks', $name, array($params));
}