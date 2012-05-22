<?php

class YouTube implements IVideo
{
    protected $model = null;
    
    public function __construct($model)
    {
        $this->model = $model;
    }

    public static function isValid($url)
    {
        return preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url);
    }

    public function getScreen($size = null)
    {
        if (is_null($this->model->url)) {
            return '';
        }

        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $this->model->url, $results);

        $vid = is_null($results) ? $this->model->url : $results[1];

        if ($size == 'small') {
            return 'http://img.youtube.com/vi/' . $vid . '/2.jpg';
        } else if ($size == 'medium') {
            return 'http://img.youtube.com/vi/' . $vid . '/1.jpg';
        } else if ($size == 'big') {
            return 'http://img.youtube.com/vi/' . $vid . '/0.jpg';
        } else {
            return 'http://img.youtube.com/vi/' . $vid . '/default.jpg';
        }
    }
    
    public function getPlayerCode($token = null)
    {
        $width = 300;
        $height = 200;
        return '<object class="v-' . $this->model->id . '" width="' . $width . '" height="' . $height . '"><param name="movie" value="'
                . str_replace('watch?v=','v/',$this->model->url) . '?fs=1&amp;showinfo=0&amp;enablejsapi=1&amp;version=3&amp;playerapiid=' . $this->model->id . '"></param>
                <param name="allowFullScreen" value="true"></param>
                <param name="wmode" value="opaque"></param>
                <param name="allowscriptaccess" value="always"></param><embed class="v-' . $this->model->id . '" src="'
                . str_replace('watch?v=','v/',$this->model->url) . '?fs=1&amp;showinfo=0&amp;enablejsapi=1&amp;version=3&amp;playerapiid=' . $this->model->id . '" type="application/x-shockwave-flash" wmode="opaque" allowscriptaccess="always" allowfullscreen="true" width="' . $width . '" height="' . $height . '">
                </embed></object>';
    }
}