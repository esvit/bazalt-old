<?php

class CMS_Form_Element_Column_Publish extends CMS_Form_Element_Column_Checkbox
{
    protected $modelName = null;

    public function __construct($name, $modelName)
    {
        parent::__construct($name);

        $this->modelName = $modelName;

        $this->width(100);

        $this->headerTemplate('cms/table/header/default');
        $this->columnTemplate('cms/table/column/publish');

        $this->javascriptTemplate('cms/table/javascript/publish');
    }

    public function callback($js)
    {
       // $js = '$(".bz-table-column-publish input").live("change", function() {' . $js . ' });';
        //Html_Form::addOnReady($js);
    }

    public function dataToString($data)
    {
        if (isset($data->{$this->name})) {
            $this->checked($data->{$this->name});
        }
        $this->table->View->assign('data', $data->id);
        $this->table->View->assign('element', $this);
        return $this->table->View->fetch($this->columnTemplate());
    }

    public function ajaxChangePublish($id, $publish)
    {
        $q = ORM::update($this->modelName)
                ->set($this->columnName, ($publish == 'true') ? 1 : 0)
                ->where('id = ?', (int)$id);

        return $q->exec();
    }
}