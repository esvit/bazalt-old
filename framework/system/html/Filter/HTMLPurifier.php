<?php

using('Framework.Vendors.HTMLPurifier');

class Html_Filter_HTMLPurifier extends Html_Filter_Base
{
    public function runFilter($element, $value)
    {
        $config = HTMLPurifier_Config::createDefault();
        foreach ($this->config as $key => $value) {
            $config->set($key, $value);
        }
        $purifier = new HTMLPurifier($config);

        return $purifier->purify($value);
    }
}