<?php

interface CMS_Interface_MessageTransport
{
    public static function init($params);//from config
    
    public static function send($address, $body, $subject = null);
    
    public static function getTitle();
}