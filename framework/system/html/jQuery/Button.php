<?php

class Html_jQuery_Button extends Html_Element_Button
{
    protected $title = null;

    protected $icon = null;

    protected $primary = false;

    protected $secondary = false;

    protected $type = 'submit';
    
    public function getValidAttributes()
    {
        $attrs = parent::getValidAttributes();
        $attrs []= 'type';
        return $attrs;
    }

    public function reset()
    {
        $this->type = 'reset';
        return $this;
    }

    public function primary()
    {
        $this->primary = true;
        return $this;
    }

    public function secondary()
    {
        $this->secondary = true;
        return $this;
    }

    public function title($title = null)
    {
        if ($title != null) {
            $this->title = $title;
            return $this;
        }
        return $this->title;
    }

    public function icon($icon = null)
    {
        if ($icon != null) {
            $this->icon = $icon;
            return $this;
        }
        return $this->icon;
    }

    public function toString()
    {
        $str = '';
        
        $this->attributes['type'] = $this->type;
        $this->attributes['value'] = $this->value;

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
        
        
        $str .= '<button ' . $attrs . '>';
        if (!empty($this->icon)) {
            $str .= '<span class="ui-icon ' . $this->icon . '"></span>';
        }

        $str .= $this->title;
        $str .= '</button>';
        return $str;
    }
}
