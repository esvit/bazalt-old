<?php

class Html_MultiForm extends Html_Form
{
    private $_subForms = array();
    protected $_namespace = 'multiform';
    private $_curSubFormName = null;

    public function __construct($name = null)
    {
        $this->_curSubFormName = 'curSubForm' . $_namespace;
        if ((strToLower($_SERVER['REQUEST_METHOD']) != 'post')) {
            unset(Session::Singleton()->{$this->_curSubFormName});
            unset(Session::Singleton()->{$this->_namespace});
        }
        if (Session::Singleton()->{$this->_namespace} == null) {
            Session::Singleton()->{$this->_namespace} = array();
        }
        parent::__construct($name);
    }
    
    public function addSubForm($form)
    {
        $this->_subForms[$form->name()] = $form;
        $form->container($this);
    }
    
    public function getSubForm($name)
    {
        return isset($this->_subForms[$name]) ? $this->_subForms[$name] : null;
    }

    public function getStoredForms()
    {
        $stored = array();
        foreach (Session::Singleton()->{$this->_namespace} as $key => $values) {
            $stored[] = $key;
        }

        return $stored;
    }

    public function getCurrentSubFormName()
    {
        if (!Session::Singleton()->{$this->_curSubFormName}) {
            reset($this->_subForms);
            Session::Singleton()->{$this->_curSubFormName} = current($this->_subForms)->name;
        }
        return Session::Singleton()->{$this->_curSubFormName};
    }
    
    public function setCurrentSubFormName($name)
    {
        Session::Singleton()->{$this->_curSubFormName} = $name;
    }
    
    public function getCurrentSubForm()
    {
        $name = $this->getCurrentSubFormName();
        if(isset($this->_subForms[$name])) {
            $values = $this->_subForms[$name]->value();
            foreach(Session::Singleton()->{$this->_namespace} as $sessionName => $values) {
                if ($sessionName == $name) {
                    unset($values['action']);
                    $this->_subForms[$name]->value($values);
                }
            }
            return $this->_subForms[$name];
        }

        return false;
    }

    public function getNextSubForm()
    {
        $curName = $this->getCurrentSubFormName();
        $i = 1;
        foreach ($this->_subForms as $name => $form) {
            if ($name == $curName && $i < count($this->_subForms)) {
                $cur = current($this->_subForms);
                if($cur->name != $name) {
                    return $cur;
                }
                return null;
            }
            $i++;
        }
        return null;
    }
    
    public function getPrevSubForm()
    {
        $curName = $this->getCurrentSubFormName();
        $prev = null;
        foreach ($this->_subForms as $name => $form) {
            if ($name == $curName && $prev != null) {
                return $this->_subForms[$prev];
            }
            $prev = $name;
        }
        return null;
    }
    
    public function getCurrentStep()
    {
        $curName = $this->getCurrentSubFormName();
        $step = 1;
        foreach ($this->_subForms as $name => $form) {
            if ($name == $curName) {
                return $step;
            }
            $step++;
        }
    }

    public function toString()
    {
        return $this->getCurrentSubForm()->toString();
    }

    public function begin()
    {
        return $this->getCurrentSubForm()->begin();
    }

    public function end()
    {
        return $this->getCurrentSubForm()->end();
    }
    
    public function offsetExists($offset)
    {
        return $this->getCurrentSubForm()->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->getCurrentSubForm()->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->getCurrentSubForm()->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->getCurrentSubForm()->offsetUnset($offset);
    }

    public function validate()
    {
        $name = $this->getCurrentSubFormName();
        $subForm = $this->getCurrentSubForm();
        // $subForm->value($_POST[$this->name][$name]);
        // $values = $subForm->value();
        // if(isset($values['action']) && $values['action'] == 'prev') {
            // $subForm = $this->getPrevSubForm();
            // if($subForm) {
                // $this->setCurrentSubFormName($subForm->name);
                // return false;
            // }
        // }
        // print_r($values);exit;
        if ($subForm->validate()) {
            $session = Session::Singleton()->{$this->_namespace};
            $session[$name] = $subForm->value();
            Session::Singleton()->{$this->_namespace} = $session;
            $nextForm = $this->getNextSubForm();

            if($nextForm) {
                $nextForm->initElement();
                $this->setCurrentSubFormName($nextForm->name);
            } else {
                unset(Session::Singleton()->{$this->_curSubFormName});
                return true;
            }
        }
        
        return false;
    }
    
    public function isPostBack()
    {
        $curName = $this->getCurrentSubFormName();
        return (strToLower($_SERVER['REQUEST_METHOD']) == 'post') && isset($_POST[$this->name][$curName]);
    }
    
    public function dataSource($dataSource = null)
    {
        if ($dataSource != null) {
            $this->dataSource = $dataSource;
            return $this;
        }
        if ($this->dataSource == null) {
            $this->dataSource = new Html_DataSource_Post($this);
        }
        return $this->dataSource;
    }
    
    public function value()
    {
        $data = array();
        foreach (Session::Singleton()->{$this->_namespace} as $name => $info) {
            if (is_array($info) && count($info)) {
                foreach($info as $key => $value) {
                    $data[$key] = $value;
                }
            }
        }
        return $data;
    }
    
    public function initElement()
    {
        parent::initElement();
        
        foreach ($this->_subForms as $name => $form) {
            $values = $this->dataSource()->values();
            $dataSource = new Html_DataSource_Array($this, $values[$form->originalName]);
            $form->dataSource($dataSource);
            $form->initElement();
        }
    }
}
