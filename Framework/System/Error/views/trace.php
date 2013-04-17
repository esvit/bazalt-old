<?php

use Framework\System\Error\Catcher;

foreach ($_GLOBAL['trace'] as $k => $item) {
    echo '<strong>';
    echo ($k+1) . ') ' . (isset($item['class']) ? $item['class'] : '');
    echo (isset($item['type']) ? $item['type'] : '');
    echo $item['function'];
    echo '</strong>';
    echo '<br />';

    if (isset($item['file']) && isset($item['line'])) {
        echo '<pre style="white-space: normal;">';
        echo relativePath($item['file'], SITE_DIR);
        echo ' : ';
        echo $item['line'];
        echo '</pre>' . "\n";
        echo '<pre style="background-color: #EEEEEE; overflow: hidden;">';
        echo Catcher::getFileLineForDebug($item['file'], $item['line']);
        echo '</pre>' . "\n";
    }

    echo '<div style="display: none">';
    echo '<pre>';
    //print_r($item['args']);
    echo '</pre>';
    echo '</div>';
    echo '<div style="clear: both"></div>' . "\n";
}