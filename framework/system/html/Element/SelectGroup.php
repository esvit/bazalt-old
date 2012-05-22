<?php

class Html_Element_SelectGroup
{
    protected $container = null;

    protected $title = null;

    protected $options = array();

    protected $values = array();

    public function title($title = null)
    {
        if ($title != null) {
            $this->title = $title;
            return $this;
        }
        return $this->title;
    }

    public function __construct(Html_Element_Select $container, $title = null)
    {
        $this->container = $container;
        $this->title = $title;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function addOption($title, $value = null)
    {
        $index = $this->container->getNextOptionIndex();
        $this->options[$index] = $title;
        if (empty($value)) {
            //$value = $this->container->id() . '_value_' . $index;
        }
        $this->values[$index] = $value;
    }

    public function toString($selectedValue, $isMain = false)
    {
        $str = '';
        if (!$isMain) {
            $str .= '<optgroup label="' . htmlspecialchars($this->title, ENT_QUOTES) . '">';
        }

        foreach ($this->options as $index => $option) {
            $selected = '';
            $value = $this->values[$index];
            if ( $this->container->multiple() ? in_array($value, $selectedValue) : ($selectedValue == $value) ) {
                $selected = ' selected="selected" ';
            }
            $str .= '<option value="' . $value . '" ' . $selected . '>' . $option . '</option>';
        }
        if (!$isMain) {
            $str .= '</optgroup>';
        }
        return $str;
    }
}
