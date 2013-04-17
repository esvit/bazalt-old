<?php

function thumb($image, $options = [])
{
    $params = [
        'w' => $options['width'],
        'h' => $options['height']
    ];
    return '/thumb.php' . $image . '?' . http_build_query($params);
}