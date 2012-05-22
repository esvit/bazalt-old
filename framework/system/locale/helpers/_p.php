<?php

function _p($string, $pluralString, $n = 0, $domain = null)
{
    return Locale_Translation::getPluralTranslation($string, $pluralString, $n, $domain);
}