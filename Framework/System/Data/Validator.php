<?php

namespace Framework\System\Data;

class Validator implements \ArrayAccess
{
    protected $data = [];

    protected $fields = [];

    protected $errors = [];

    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function errors()
    {
        return $this->errors;
    }

    public function field($name)
    {
        return $this->fields[$name] = new Field($name, $this);
    }

    public function getData($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
    
    public function validate()
    {
        $valid = true;
        foreach ($this->fields as $name => $field) {
            $messages = [];
            $valid &= $field->validate($this->getData($name), $messages);
            if (count($messages) > 0) {
                $this->errors[$name] = $messages;
            }
        }
        return $valid;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}