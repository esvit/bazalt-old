<?php

abstract class Html_ContainerElement extends Html_FormElement implements ArrayAccess
{
    protected $form = null;

    protected $dataSource = null;

    /**
     * Container elements
     *
     * @var array
     * @see HtmlFormElement
     */
    protected $elements = array();

    public function initAttributes()
    {
        parent::initAttributes();

        $this->template('elements/container');
    }

    public function dataSource($dataSource = null)
    {
        if ($dataSource != null) {
            $this->dataSource = $dataSource;
            return $this;
        }
        if ($this->dataSource == null) {
            $dataSource = $this->container->dataSource();

            $values = $dataSource->values();
            if ($this->prependsName()) {
                $values = isset($values[$this->originalName]) ? $values[$this->originalName] : $values;
            }
            return new Html_DataSource_Array($this, $values);
        }
        return $this->dataSource;
    }

    public function addElement($name, $elementName = null, $options = array())
    {
        if (is_string($name)) {
            $class = Html_Form::getRegistredClass($name);
            if (!$elementName) {
                $elementName = Html_Element::generateId($class);
            }
            $element = new $class($elementName, $options);
        } else {
            $element = $name;
        }
        if (!($element instanceof Html_FormElement)) {
            throw new Html_Exception_Element('Element must be Html_FormElement');
        }

        if ($this->form == null) {
            throw new Exception('Element must add to form');
        }
        $name = $element->getOriginalName();
        if (empty($name)) {
            throw new Exception('Element must have a name');
        }
        $element->container($this);
        $element->form($this->form());
        $this->elements[$name] = $element;
        return $element;
    }
    
    public function findElement($name)
    {
        $this->initElement();
        
        if(isset($this->elements[$name])) {
            return $this->elements[$name];
        }
        $e = null;
        foreach ($this->elements as $elem) {
            if($elem instanceof Html_ContainerElement) {
                $e = $elem->findElement($name);
                if($e != null) {
                    return $e;
                }
            }
        }
        return $e;
    }
    
    public function findElementByID($name)
    {
        $this->initElement();
        
        foreach ($this->elements as $elem) {
            if ($elem->id() == $name) {
                return $elem;
            }
        }
        $e = null;
        foreach ($this->elements as $elem) {
            if($elem instanceof Html_ContainerElement) {
                $e = $elem->findElementByID($name);
                if($e != null) {
                    return $e;
                }
            }
        }
        return $e;
    }
    
    public function removeElement($name)
    {
        if(isset($this->elements[$name])) {
            unset($this->elements[$name]);
        }
        if(isset($this->form->elements[$name])) {
            unset($this->form->elements[$name]);
        }
    }

    public function addSubform(Html_Form $form)
    {
        $form->container = $this;
        $form->subform(true);
        return $this->addElement($form, $form->name());
    }

    public function toString()
    {
        $str = '';

        foreach ($this->elements as $el) {
            if (is_object($el)) {
                $str .= $el->toString() . "\n";
            }
        }
        $view = $this->view();
        $view->assign('element', $this);
        $view->assign('content', $str);

        return parent::toString();
    }

    public function removeElements()
    {
        $this->elements = array();
    }

    public function clearValidators()
    {
        foreach ($this->elements as $el) {
            $el->clearValidators();
        }
        $this->validators = array();
    }

    public function validate()
    {
        $result = true;
        foreach ($this->elements as $el) {
            $res = $el->validate();
            $result = $result & $res;
        }
        return $result;
    }

    protected function filterValue($element, $value)
    {
        if (count($element->Filters) == 0) {
            return $value;
        }
        foreach ($element->Filters as $filter) {
            $value = $filter->runFilter($element, $value);
        }
        return $value;
    }

    public function dataBind(ORM_Record $object)
    {
        $this->initElement();
        foreach($this->elements as $element) {
            if ($element->prependsName() && !$this->form->isPostBack()) {
                if (isset($object->{$element->getOriginalName()})) {
                    $element->value($object->{$element->getOriginalName()});
                }
            }
            if ($element instanceOf Html_ContainerElement) {
                $element->dataBind($object);
            }
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->elements[$offset]) ? $this->elements[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->elements[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }
    
    public function initElement()
    {
        if ($this->isInited) {
            return;
        }
        parent::initElement();

        foreach ($this->elements as $elem) {
            $elem->initElement();
        }
    }

    public function __clone()
    {
        parent::__clone();

        $elements = array();

        foreach ($this->elements as $k => $element) {
            $el = clone $element;
            $el->container($this)
               ->form($this->form());
            $elements[$k] = $el;
        }
        $this->elements = $elements;
    }
}