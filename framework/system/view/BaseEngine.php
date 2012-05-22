<?php

abstract class View_BaseEngine
{
    abstract function fetch($folder, $file, View_Base $view);

    abstract function setLocaleDomain($domain);

    abstract function getLocalizationStrings($file);
}