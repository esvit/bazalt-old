<?php

function bz_toolbar_filter_label($content, $args = array())
{
    $css = array();
    if (array_key_exists('active', $args) && $args['active'] == 'true') {
        $css []= 'active';
    }
    $id = '';
    if (array_key_exists('id', $args)) {
        $id = 'id="' . $args['id'] . '"';
    }
    return '<a ' . $id . ' class="' . implode(' ', $css) . '" href="javascript: void(null);">' . $content . ' <span class="ui-helper-hidden">(0)</span></a>';
}