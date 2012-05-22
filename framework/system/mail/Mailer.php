<?php

using('Framework.Vendors.PHPMailer');

class Mail_Mailer extends PHPMailer implements IWebConfig
{
    protected static $instance = null;

    protected $type = 'mail';

    protected $from = null;

    private static $allowAttributes = array(
        'type',
        'from'
    );

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct(true);

        $this->CharSet = 'UTF-8';
    }

    public function loadWebConfig($node)
    {
        foreach ($node as $elem) {
            $name = $elem->name();
            $value = DataType_String::replaceConstants($elem->value());
            if (!in_array($name, self::$allowAttributes)) {
                throw new Exception('Denied attribute ' . $name);
            }
            if ($value == 'false') {
                $value = false;
            }
            if ($value == 'true') {
                $value = true;
            }
            $this->$name = $value;
        }
        return $this;
    }

    public function send()
    {
        switch ($this->type) {
        case 'sendmail':
            $this->IsSendmail();
            break;
        case 'smtp':
            $this->IsSMTP();
            break;
        }
        if (!empty($this->from)) {
            $this->SetFrom($this->from);
        }
        return parent::send();
    }
}