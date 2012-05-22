<?php

class CMS_Form_Element_Column_Actions extends CMS_Form_Element_TableColumn
{
    const ACTION_DELETE = 'delete';

    const ACTION_TYPE_URL = 'url';
    const ACTION_TYPE_CALLBACK = 'callback';
    
    protected $actions = array();

    public function __construct($actions = array())
    {
        parent::__construct('actions');
        
        $this->actions = $actions;

        $this->headerTemplate('cms/table/header/default');
        $this->columnTemplate('cms/table/column/actions');
    }
    
    public function initElement()
    {
        parent::initElement();
        
        
    }

    public function dataToString($data)
    {
        // $params = $this->actions;
        // $mappers = array();
        $actions = array();
        
        $this->table->View->assign('item', $data);
        $this->table->View->assign('data', $data->{$this->name});
        
        foreach($this->actions as $k => $action) {
            if(is_string($action)) {
                switch($action) {//default actions
                    case self::ACTION_DELETE:
                        $jsObj = sprintf('window.%s.elements.%s_0', $this->table()->name(), $this->table()->name());
                        $actions []= array(
                            'type' => self::ACTION_TYPE_CALLBACK,
                            'jsObj' => $jsObj,
                            'method' => 'Delete',
                            'callback' => 'function() {
                                var row = el.parents("tr.bz-table-row"); 
                                row.hide(100, function() { 
                                    row.remove(); 
                                    '.$jsObj.'.unlock(); 
                                }); 
                            }()',
                            'title' => __('Delete', ''),
                            'confirm' => __('Are you realy want to delete this record ?', ''),
                            'iconClass' => 'icon-white icon-trash'
                        );
                    break;
                }
            } else if(is_array($action)) {
                $fp = $action[0];
                $actn = array(
                    'title' => $action['title'],
                    'iconClass' => isset($action['iconClass']) ? $action['iconClass'] : '',
                    'confirm' => isset($action['confirm']) ? $action['confirm'] : ''
                );
                if(is_string($fp)) {//mapper
                    $mapper = $fp;
                    $mapperParams = $action[1];
                    foreach($mapperParams as $p => $field) {
                        if(isset($data->{$field})) {
                            $mapperParams[$p] = $data->{$field};
                        }
                    }
                    $actn += array(
                        'type' => self::ACTION_TYPE_URL,
                        'jsObj' => sprintf('window.%s.elements.%s_0', $this->table()->name(), $this->table()->name()),
                        'url' => CMS_Mapper::urlFor($mapper, $mapperParams)
                    );
                } else {//callback
                    $element = $fp;
                    $jsObj = sprintf('window.%s.elements.%s_0', $element->name(), $element->name());
                    $method = substr($action[1], 4);
                    $actn += array(
                        'type' => self::ACTION_TYPE_CALLBACK,
                        'jsObj' => $jsObj,
                        'method' => $method,
                        'callback' => isset($action['callback']) ? $action['callback'] : 'function() { '.$jsObj.'.updateData(); }'
                    );
                }
                $actions []= $actn;
            }
        }
        $this->table->View->assign('actions', $actions);
        $this->table->View->assign('element', $this);
        return $this->table->View->fetch($this->columnTemplate());
    }
}
