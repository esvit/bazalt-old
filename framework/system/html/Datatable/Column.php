<?php

class Html_Datatable_Column
{
    public $render = null;

    public $visible = true;

    public $sortable = true;

    public $targets = null;

    public $name = null;

    public $title = null;

    public $index = 0;

    public $realIndex = 0;

    public $searchable = true;

    public $sorting = null;

    public $container = null;

    public $width = null;

    public $cssClass = null;

    /**
     * Додає умови пошуку у колекцію
     *
     * @param ORMCollection $collection Колекція
     * @param string        $string     Пошуковий запит
     */
    public function addSearchCondition($collection, $string)
    {
        if (!$this->searchable || empty($this->name)) {
            return;
        }
        $collection->orWhere('`f`.`' . $this->name . '` LIKE ?', '%' . $string . '%');
    }

    public function __construct($name = null, $title = null)
    {
        $this->name = $name;
        if (empty($this->name)) {
            $this->sortable = false;
        }
        $this->title = ($title == null) ? $name : $title;
    }

    public function search($search, ORM_Record $item)
    {
        if (empty($this->name)) {
            return false;
        }
        $data = $item->{$this->name};

        return (stripos($data, $search) !== false);
    }
    
    public function addRowCallback($js)
    {
        $js = 'var data = aData[' . $this->index . '];' . "\n" . $js;
        $this->container->addRowCallback($js);
    }

    public function addRedrawCallback($js)
    {
        $this->container->addRedrawCallback($js);
    }

    public function renderHeader()
    {
        return '<th>' . $this->title . '</th>';
    }

    public function renderFooter()
    {
        return $this->renderHeader();
    }

    public function toString()
    {
        $js = '{';

        if ($this->render != null) {
            $js .= '"fnRender" : function(obj){ ' . $this->render . ' },';
        }
        if ($this->cssClass != null) {
            $js .= '"sClass" : "' . $this->cssClass . '",';
        }
        $js .= '"bSortable" : ' . ($this->sortable ? 'true' : 'false') . ',';

        $js .= '"aTargets" : [' . $this->index . '],';

        if ($this->name != null) {
            $js .= '"sName" : ' . Html_Datatable_Table::jsEscape($this->name) . ',';
        }

        if ($this->title != null) {
            $js .= '"sTitle" : ' . Html_Datatable_Table::jsEscape($this->title) . ',';
        }

        if ($this->width != null) {
            if (is_numeric($this->width)) {
                $this->width .= 'px';
            }
            $js .= '"sWidth" : "' . $this->width . '",';
        }

        if (!$this->visible) {
            $js .= '"bVisible" : false,';
        }

        $js = substr($js, 0, -1);
        $js .= '}' . "\n";
        return $js;
    }

    public function getData(ORM_Record $item)
    {
        if (empty($this->name)) {
            return null;
        }
        return $item->{$this->name};
    }

    public function __toString()
    {
        return $this->toString();
    }
}
