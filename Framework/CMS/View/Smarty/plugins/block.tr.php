<?php

function escapeJavaScriptText($string)
{
    return str_replace("\n", '\n', str_replace('"', '\"', addcslashes(str_replace("\r", '', (string)$string), "\0..\37'\\")));
}

function smarty_block_tr($params, $content, &$template, &$repeat)
{
    $text = stripslashes($content);

    $themeName = CMS_Theme::getCurrentTheme()->Alias;
    $component = $template->getTemplateVars('component');

    if ($component) {
        $component = get_class($component);
    }

    if (isset($params['escape'])) {
        $escape = $params['escape'];
        unset($params['escape']);
    }

    if (empty($component)) {
        $component = $themeName;
    }
    if (isset($params['component'])) {
        $component = trim($params['component']);
        unset($params['component']);
    }

    if (isset($params['assign'])) {
        $assign = $params['assign'];
        unset($params['assign']);
    }

    if (isset($params['plural'])) {
        $plural = $params['plural'];
        unset($params['plural']);

        if (isset($params['count'])) {
            $count = $params['count'];
            unset($params['count']);
        }
    }

    if (isset($count) && isset($plural)) {
        $trText = _p($text, $plural, $count);
    } else { // use normal
        $trText = __($text, $component);
    }

    // if component not have translate
    if ($trText == $text) {
        $trText = __($text, $themeName);
    }

    // run strarg if there are parameters
    if (count($params)) {
        $trText = DataType_String::format($trText, $params);
    }

    if (!isset($escape) || $escape == 'html') { // html escape, default
       $trText = nl2br(htmlspecialchars($trText));
    } elseif (isset($escape) && ($escape == 'javascript' || $escape == 'js')) { // javascript escape
       $trText = escapeJavaScriptText($trText);//str_replace('\'','\\\'',stripslashes($trText));
    }

    if (isset($assign)) {
        $template->assign($assign, $trText);
        return '';
    }
    return $trText;
}