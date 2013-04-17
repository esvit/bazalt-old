<?php

namespace Framework\System\Data;

class Field
{
    protected $name = null;

    protected $messages = [];

    protected $validators = [];

    protected $depends = [];

    protected $validator = [];

    public function __construct($name, $validator)
    {
        $this->validator = $validator;
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }
    
    public function validate($value, &$messages = [])
    {
        $valid = true;
        foreach ($this->validators as $name => $validator) {
            if (!$res = $validator($value)) {
                $messages[$name] = isset($this->messages[$name]) ? $this->messages[$name] : null;
            }
            $valid &= $res;
        }
        return $valid;
    }

    public function validator($name, $function, $message = null, $depends = [])
    {
        $this->validators[$name] = $function;
        $this->messages[$name] = $message;
        $this->depends += $depends;

        return $this;
    }

    public function required()
    {
        return $this->validator('required', function($value) {
            $value = trim($value);
            return !empty($value);
        }, 'Field cannot be empty');
    }

    public function email()
    {
        return $this->validator('email', function($value) {
            $value = trim($value);
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }, 'Invalid email');
    }

    public function equal($field)
    {
        return $this->validator('equal', function($value) use ($field) {
            $value2 = $this->validator->getData($field->name());
            return $value === $value2;
        }, 'Fields not equals');
    }
}