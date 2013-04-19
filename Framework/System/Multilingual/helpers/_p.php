<?php

function _p($string, $pluralString, $n = 0, $domain = null)
{
    $tr = Framework\System\Multilingual\Domain::getDomain($domain);
    if ($tr) {
        return $tr->translate($string, $pluralString, $n);
    }
    return $string;
}