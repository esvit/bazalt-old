<?php

function bz_toolbar_buttons_set($content, $args = array())
{
    $class = $args['class'];
    
    return '<div class="' . $class . '">' . $content . '</div>';
}