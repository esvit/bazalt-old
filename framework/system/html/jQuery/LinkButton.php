<?php

class Html_jQuery_LinkButton extends Html_jQuery_Button
{
    protected $link = '#';

    public function link($link = null)
    {
        if ($link !== null) {
            $this->link = $link;
            return $this;
        }
        return $this->link;
    }

    public function toString()
    {
        $str = '';
        $this->invalidAttribute('type');

        $this->addClass('fg-button ui-state-default ui-corner-all');

        if ($this->primary) {
            $this->addClass('ui-priority-primary');
        } else if ($this->secondary) {
            $this->addClass('ui-priority-secondary');
        }
        if (!empty($this->icon)) {
            $this->addClass('fg-button-icon-left');
        }

        $attrs = $this->getAttributesString();

        $str .= '<a href="' . $this->link . '" style="float: none;" ' . $attrs . '>';

        if (!empty($this->icon)) {
            $str .= '<span class="ui-icon ' . $this->icon . '"></span>';
        }

        $str .= $this->title;
        $str .= '</a>';
        return $str;
    }
}