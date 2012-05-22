<?php

class CMS_Form_Element_Column_Numeric extends CMS_Form_Element_TableColumn
{
    protected $attributes = array();

    public function __construct($name, $attributes = array())
    {
        Scripts::addModule('ui-spinner');
    
        parent::__construct($name);

        $this->width(100);
        
        if(!isset($attributes['min'])) {
            $attributes['min'] = 0;
        }
        if(!isset($attributes['max'])) {
            $attributes['max'] = 200;
        }
        
        $this->attributes = $attributes;

        $this->headerTemplate('cms/table/header/default');
        $this->columnTemplate('cms/table/column/numeric');
    }

    public function callback($callback)
    {
        $js = '$(".spinner").spinner({showOn: \'both\'}).change(function(){ ' . $callback . ' });';
        Html_Form::addOnReady($js);
    }

    public function dataToString($data)
    {
        // if (isset($data->{$this->name})) {
            // $this->checked($data->{$this->name});
        // }
        // print_r($data->{$this->name});
        // print_r($this->attributes);
        // exit;
        $this->table->View->assign('data', $data);
        $this->table->View->assign('value', $data->{$this->name});
        $this->table->View->assign('attributes', $this->attributes);
        $this->table->View->assign('element', $this);
        return $this->table->View->fetch($this->columnTemplate());
    }
}
