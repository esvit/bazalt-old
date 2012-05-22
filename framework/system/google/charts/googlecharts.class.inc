<?php

class GoogleCharts extends Object
{
    const BASE_URL = 'http://chart.apis.google.com/chart';

    public static function encodeNum($dec)
    {
        if ($dec === false) {
            return '__';
        }

        $e = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
             'abcdefghijklmnopqrstuvwxyz' . 
             '0123456789-.';
        $m = strlen($e); // == 63
        $res = '';

        do {
            $r = $dec % $m;
            $dec = (int)($dec / $m);
            $res = $e[$r].$res;
        } while($dec > 0);

        $res = sprintf("%'A2s", $res);
        return $res;
    }

    public static function getHtml($type, $width, $height, $data, 
                                   $labels = false, $legend = false,
                                   $colors = false, $alt = '')
    {
        $edata = implode('', (array_map(array('self', 'encodeNum'), $data)));

        $url = self::BASE_URL . '?cht=' . $type . '&chd=e:' . $edata . 
                                '&chs=' . $width . 'x' . $height;

        if (is_array($colors) && count($colors)) {
            $url .= '&chco='.implode(',', $colors);
        }

        if(is_array($labels) && count($labels)) {
            $url .= '&chl='.implode('|', (array_map("urlencode", $labels)));
        }

        // legend
        if(is_array($legend) && count($legend)) {
            $url .= '&chdl=' . implode('|', (array_map("urlencode", $legend)));
            $url .= '&chdlp=r';
        }

        return '<img src="' . $url . '" width="' . $width . '" ' . 
                    'height="' . $height . '" alt="' . $alt . '" />';
    }
}