<?php

use Framework\CMS as CMS;

function twig_format_date_filter($date, $format)
{
return CMS\User::get()->formatDate($format, strtotime($date));
}

function twig_format_time_filter($time, $format)
{
    $dt = new \DateTime('@' . $time, new \DateTimeZone('UTC'));
    $str = '';
    $time = array('days'    => $dt->format('z'),
    'hours'   => $dt->format('G'),
    'minutes' => $dt->format('i'),
    'seconds' => $dt->format('s'));
    if ($time['days'] > 0) {
        $str .= $time['days'] . ' ' . _p('day', 'days', $time['days'], 'CMS') . ' ';
    }
    if ($time['hours'] > 0) {
        $str .= $time['hours'] . ' ' . _p('hour', 'hours', $time['hours'], 'CMS') . ' ';
    }
    if ($time['minutes'] > 0) {
        $str .= $time['minutes'] . ' ' . _p('minute', 'minutes', $time['minutes'], 'CMS') . ' ';
    }
    $str .= $time['seconds'] . ' ' . _p('second', 'seconds', $time['seconds'], 'CMS');
    return $str;
}

function twig_number_filter($number, $decimals = 0, $dec_point = '.', $thousands_sep = ',')
{
return number_format($number, $decimals, $dec_point, $thousands_sep);
}

function twig_truncate_filter($string, $size = 200)
{
return truncate($string, $size, true, true);
}

function twig_print_r_filter($obj)
{
return print_r($obj, true);
}

function twig_instanceof($obj, $class)
{
return ($obj instanceof $class);
}

function twig_class_name_filter($obj)
{
return get_class($obj);
}

function twig_sortby_filter($array, $clause, $ascending = true)
{
Helper\ArrayHelper::sortBy($array, $clause, $ascending);
return $array;
}

function replace_nofollow($matches)
{
$host = Helper\Url::getHostname();
if ($matches['url'][0] == '/' || strpos($matches['url'], $host) === 0 || strpos($matches['url'], 'http://mistinfo.com') === 0) {
return $matches[0];
}
return '<a rel="nofollow external" class="external" target="_blank" href="' . $matches['url'] . '">';
    }

    function twig_nofollow_filter($content)
    {
    return preg_replace_callback('/<a(?P<attr>.*)href=[\"\'](?P<url>[^\"]*?)[\"\'](?P<attr2>.*)>/isU', 'replace_nofollow', $content);
        }