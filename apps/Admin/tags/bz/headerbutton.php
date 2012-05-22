<?php

function bz_headerbutton($content, $args = array())
{
    $url = $args['url'];

    $icon = '';
    if (isset($args['icon'])) {
        $icon = '<i class="' . $args['icon'] . '"></i> ';
    }
    return '<a href="' . $url . '" class="btn">' .
           $icon . $content . 
           '</a>';
}