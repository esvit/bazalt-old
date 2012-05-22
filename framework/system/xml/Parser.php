<?php

class XML_Parser
{
    public static function parse($fileName, $class = null)
    {
        $el = simplexml_load_file($fileName);
        if ($el === false) {
            throw new XML_Exception('Invalid xml file ' . $fileName);
        }
        return XML_Node::fromSimpleXml($el, $class);
    }

    public static function parseString($string, $class = null)
    {
        $el = simplexml_load_string($string);
        if ($el === false) {
            throw new XML_Exception('Invalid xml string "' . $string . '"');
        }
        return XML_Node::fromSimpleXml($el, $class);
    }
}