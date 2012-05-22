<?php

class Vimeo implements IVideo
{
    protected $model = null;
    
    public function __construct($model)
    {
        $this->model = $model;
    }

    public static function isValid($url)
    {
        return preg_match('/http:\/\/vimeo\.com\/([^\\?\\&]+)/', $url);
    }
    
    function getScreen($size = null)
    {
        if (is_null($this->model->url)) {
            return '';
        }

        //$c = Url::getContent('http://vimeo.com/api/v2/video/6271487.json');
        //$res = json_decode($c);
        preg_match('/http:\/\/vimeo\.com\/([^\\?\\&]+)/', $this->model->url, $results);
        
        $url = 'http://vimeo.com/api/v2/video/' . $results[1] . '.json';
        
        $c = Url::getContent($url);
        $res = json_decode($c);
        //$vid = is_null($results) ? $url : $results[1];

        if ($size == 'small') {
            return $res[0]->thumbnail_small;
        } else if ($size == 'medium') {
            return $res[0]->thumbnail_medium;
        } else if ($size == 'big') {
            return $res[0]->thumbnail_large;
        } else {
            return $res[0]->thumbnail_large;
        }
    }
    
    public function getPlayerCode($token = null)
    {
        return '<iframe class="vimeo" id="player_' . $this->model->id . '" src="'.str_replace('vimeo.com/','player.vimeo.com/video/',$this->model->url).'?show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;js_api=1&amp;js_swf_id=player_' . $this->model->id . '" width="706" height="360" frameborder="0"></iframe>';
    }
}