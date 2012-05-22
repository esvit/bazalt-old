<?php

class Html_Datatable_Column_Bool extends Html_Datatable_Column
{
    public $callback = null;

    public function __construct($name, $title = null)
    {
        parent::__construct($name, $title);

        $this->width = 20;

        $this->cssClass = 'ui-column-center';

        $this->render = 'var data = obj.aData[obj.iDataColumn];
            var name = "chk" + obj.iDataColumn + "_" + obj.iDataRow;
            var value = \'\';
            if (data == true || data == "1") {
                value = \'checked="checked"\';
            }
            var html = \'<input type="checkbox" autocomplete="off" id="\' + name + \'" \'+value+\' />\' +
            \'<label for="\' + name + \'"></label>\';

            return html;';
    }
    
    protected function addJs()
    {
        $js = '';
        if ($this->callback != null) {
            $js .= '$("td:nth-child('. $this->realIndex .')", nRow).find("input").unbind("click").click(function() { 
            var id = ($(".row-id", nRow).size() > 0) ? $(".row-id", nRow).val() : ($.oTable.fnGetData(nRow)[0]);
            ' . $this->callback . '(id, this.checked); })';
        }
        $this->addRowCallback($js . ';');
    }

    /**
     * Тип стовпця залежить від поля ід, тобто потрбіно щоб в таблиці були включені групові операції або щоб 1 стовпець був hidden і містив ід
     */
    public function toString()
    {
        $this->addJs();

        return parent::toString();
    }
}
