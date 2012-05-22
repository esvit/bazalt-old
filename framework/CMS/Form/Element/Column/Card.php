<?php

class CMS_Form_Element_Column_Card extends CMS_Form_Element_TableColumn
{
    protected $params = array();

    public function __construct($name, $params = array())
    {
    
        parent::__construct($name);
        
        $this->params = array(
            'mapper' => $params[0],
            'mapperAttrs' => $params[1]
        );
        
        if(isset($params['descField'])) {
            $this->params['descField'] = $params['descField'];
            $this->params['descLength'] = isset($params['descLength']) ? $params['descLength'] : 100;
        }
        if(isset($params['imageField'])) {
            $this->params['imageField'] = $params['imageField'];
            $this->params['imageSize'] = isset($params['imageSize']) ? $params['imageSize'] : 'big';
        }

        $this->headerTemplate('cms/table/header/default');
        $this->columnTemplate('cms/table/column/card');
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
        if(isset($this->params['descField'])) {
            $this->table->View->assign('content', $data->{$this->params['descField']});
            $this->table->View->assign('contentLength', $this->params['descLength']);
        }
        if(isset($this->params['imageField'])) {
            $this->table->View->assign('image', CMS_Image::getThumb($data->{$this->params['imageField']}, $this->params['imageSize']));
        }
        $this->table->View->assign('params', $params);
        $this->table->View->assign('element', $this);
        return $this->table->View->fetch($this->columnTemplate());
    }
}
