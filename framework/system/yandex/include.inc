<?php

class YandexTCY
{
    public function getCY($url)
    {
        $uri = 'http://bar-navig.yandex.ru/u?ver=2&url=' . urlencode($url) . '&show=1';
        $content = Url::getContent($uri); // считываем файл

        // выискиваем параметр тИЦ
        preg_match("/<tcy rang=\"(.*)\" value=\"(.*)\"\/>/isU", $content, $cy);
        // возвращаем полученное значение
        return (int) $cy[2];
    }
/*
    function yandex_tic($url){
            $file=file_get_contents("http://search.yaca.yandex.ru/yca/cy/ch/$url/");
    if(preg_match("!&#151;\s+([0-9]{0,8})<\/b>!is",$file,$ok)){
                $str=$ok[1];
            }
            else if(preg_match("!<td class=\"current\" valign=\"middle\">(.*?)</td>\n</tr>!si", $file, $ok)){
                    if(preg_match("!<td align=\"right\">(.*?)</td>\n</tr>!si", $ok[0], $str)){
                        $str=$str[1];
                    } else {
                        $str=0;
                    }
            }
            else {
                $str=0;
            }

    return trim($str);
    } */
}