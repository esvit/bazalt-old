<?php

function bz_button($content, $args = array())
{
    $button = '<button ';
    $css = array('bz-button');
    //$css []= 'fg-button' ui-corner-all ui-state-default';
    // priority
    if (array_key_exists('priority', $args)) {
        switch ($args['priority']) {
        case 'primary': $css []= 'ui-priority-primary'; break;
        case 'secondary': $css []= 'ui-priority-secondary'; break;
        }
    }
    if (array_key_exists('icon', $args)) {
        $css []= 'bz-button-icon';
        $content = '<span class="ui-button-icon-primary ui-icon ' . $args['icon'] . '"></span>' . $content;
    }
    //disabled
    if (array_key_exists('disabled', $args) && $args['disabled'] == 'true') {
        $button .= ' disabled="disabled"';
    }
    //title
    if (array_key_exists('title', $args)) {
        $button .= ' title="' . $args['title'] . '"';
    }
    if (array_key_exists('id', $args)) {
        $button .= ' id="' . $args['id'] . '"';
    }
    if (array_key_exists('style', $args)) {
        $button .= ' style="' . $args['style'] . '"';
    }
    if (array_key_exists('class', $args)) {
        $css []= $args['class'];
    }
    if (array_key_exists('onclick', $args)) {
        $button .= ' onclick="' . $args['onclick'] . '"';
    }
    //$css []= 'ui-state-default';
    $button .= ' class="' . implode(' ', $css) . '"';
    return $button . ' type="button">' . $content . '</button>';
}