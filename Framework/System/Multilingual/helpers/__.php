<?php

function __($string, $domain = null)
{
    $tr = Framework\System\Multilingual\Domain::getDomain($domain);
    if ($tr) {
        return $tr->translate($string);
    }
    return $string;
}