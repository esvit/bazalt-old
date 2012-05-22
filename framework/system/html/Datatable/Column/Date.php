<?php

class Html_Datatable_Column_Date extends Html_Datatable_Column
{
    public function __construct($name, $title = null)
    {
        parent::__construct($name, $title);

        $this->width = 100;

        $this->render = '
            var data = obj.aData[obj.iDataColumn];
            if (data == "-") {
                return data;
            }
            var date = new Date(data * 1000); // street magic

            var html = \'<div class="ui-table-content-date" title="\' + date.toString("d.MM.yyyy HH:mm:ss") + \'">\';
                if (Date.today().toString("d.MM.yyyy") != date.toString("d.MM.yyyy")) {
                    html += \'<span class="ui-icon ui-icon-calendar ui-float-left"></span>\' + date.toString("d.MM.yyyy");
                    html += \'<div class="spacer"></div>\';
                }
                html += \'<span class="ui-icon ui-icon-clock ui-float-left"></span>\' + date.toString("HH:mm");
                html += "</div>";

            return html;';
    }

    public function getData(ORM_Record $item)
    {
        $date = $item->{$this->name};
        if (empty($date)) {
            return '-';
        }
        return strtotime($item->{$this->name});
    }
}