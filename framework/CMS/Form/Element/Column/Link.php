<?php

class CMS_Form_Element_Column_Link extends CMS_Form_Element_TableColumn
{
    protected $params = array();

    public function __construct($name, $params = array())
    {
    
        parent::__construct($name);
        
        $this->params = array(
            'mapper' => $params[0],
            'mapperAttrs' => $params[1]
        );

        $this->headerTemplate('cms/table/header/default');
        $this->columnTemplate('cms/table/column/link');
    }

    public function dataToString($data)
    {
        $params = $this->params;
        $this->table->View->assign('item', $data);
        $this->table->View->assign('data', $data->{$this->name});
        if(isset($this->params['mapperAttrs']) && count($this->params['mapperAttrs']) > 0) {
            foreach($this->params['mapperAttrs'] as $k => $field) {
                if(isset($data->{$field})) {
                    $params['mapperAttrs'][$k] = $data->{$field};
                }
            }
        }
        $this->table->View->assign('params', $params);
        $this->table->View->assign('element', $this);
        return $this->table->View->fetch($this->columnTemplate());
    }
}
