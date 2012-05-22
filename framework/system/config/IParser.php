<?php

interface Config_IParser
{
    public static function parseConfigNode(Config_Node $node, Config_Node $nsNode);

    public static function parseConfigAttributes($key, $value, Config_Node &$node);
}