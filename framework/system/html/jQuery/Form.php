<?php

class Html_jQuery_Form extends Html_Element_Form implements IEventable
{
    protected static $onReadyScripts = array();

    public static function addOnReady($src)
    {
        self::$onReadyScripts []= $src;
    }

    public function end()
    {
        if (count(self::$onReadyScripts) > 0) {
            $js = implode("\n", self::$onReadyScripts);
            Scripts::addInline('jQuery(document).ready(function(){' . "\n" . $js . "\n" . '});' . "\n", __CLASS__);
        }
        return parent::end();
    }

    public function addTextarea($name)
    {
        return $this->addElement(new Html_jQuery_Textarea($name));
    }
}