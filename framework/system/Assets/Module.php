<?php

class Assets_Module
{
    protected $file = null;

    protected $path = null;

    protected $moduleConfig = null;

    protected $scripts = array();

    public function __construct($file)
    {
        $this->file = $file;

        $this->path = dirname($file);

        $this->moduleConfig = WebConfig::load($file);

        $info = $this->moduleConfig->node('module');

        $scripts = $this->moduleConfig->node('scripts');
        if ($scripts) {
            foreach ($scripts->nodes('file') as $file) {
                $fileName = $file->value();
                $compress = $file->attribute('compress') == 'true';
                $this->scripts[$fileName] = $compress;
            }
        }
    }
}