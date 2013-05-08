<?php

namespace Widgets\GoogleMap;

use Framework\CMS as CMS;

class Widget extends CMS\Widget
{
    public function fetch()
    {
        if (isset($this->options['address'])) {
            $this->view()->assign('address', urlencode($this->options['address']));
        }
        $this->view()->assign('options', $this->options);
        return parent::fetch();
    }

    public function getConfigPage()
    {
        if (!isset($this->options['width'])) {
            $this->options['width'] = 200;
        }
        if (!isset($this->options['height'])) {
            $this->options['height'] = 200;
        }
        $this->view()->assign('options', $this->options);
        return $this->view()->fetch('settings');
    }
}