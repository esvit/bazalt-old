<?php

$components = glob(__DIR__ . '/../*/Component.php');

foreach ($components as $file) {
    $content = file_get_contents($file);

    $pattern = '/connect\(([\'\"])([^\'\"]*)\\1(.*)\)/i';
    
    preg_match_all($pattern, $content, $matches);
    if (count($matches[0])) {
        foreach ($matches[2] as $match) {
            echo 'INSERT INTO com_seo_routes (`name`) VALUES ("' . $match . '");' . "\n";
        }
    }
}