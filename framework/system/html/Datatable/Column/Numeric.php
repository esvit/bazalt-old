<?php

class Html_Datatable_Column_Numeric extends Html_Datatable_Column
{
    public $callback = null;

    public function __construct($name, $title = null, $attributes = array())
    {
        parent::__construct($name, $title);

        $this->width = 60;
        
        if(!isset($attributes['min'])) {
            $attributes['min'] = 0;
        }
        if(!isset($attributes['max'])) {
            $attributes['max'] = 200;
        }

        $this->render = 'var data = obj.aData[obj.iDataColumn];
            var name = "chk" + obj.iDataColumn + "_" + obj.iDataRow;

            var html = \'<input autocomplete="off" style="width: ' . $this->width . 'px" id="\' + name + \'" class="editable-number spinner" type="text" min="'.$attributes['min'].'" max="'.$attributes['max'].'" value="\'+data+\'"/>\';

            return html;';
    }

    public function toString()
    {
        $this->addRedrawCallback('$(".spinner").spinner({showOn: \'both\'}).change(function(){ ' . $this->callback . ' });');

        return parent::toString();
    }
}
