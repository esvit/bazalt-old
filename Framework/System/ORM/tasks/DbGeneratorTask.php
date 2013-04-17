<?php

define('DEBUG', true);
define('SITE_DIR', dirname(__FILE__));

require_once 'phing/Task.php';
require_once dirname(__FILE__) . '/../../../core/include.inc';

using('Framework.System.ORM');
using('Framework.System.ORM.Generator');

class DbGeneratorTask extends Task
{
    private $host = null;

    private $user = null;

    private $password = null;

    private $name = null;

    private $out = null;

    private $path = null;

    public function setHost($value)
    {
        $this->host = $value;
    }

    public function setUser($value)
    {
        $this->user = $value;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function setPath($value)
    {
        $this->path = $value;
    }

    public function setOut($value)
    {
        $this->out = $value;
    }

    /**
     * The init method: Do init steps.
     */
    public function init()
    {
      // nothing to do here
    }

    /**
     * The main entry point method.
     */
    public function main()
    {
        $options = array(
            'server'   => $this->host,
            'database' => $this->name,
            'username' => $this->user,
            'password' => $this->password
        );

        ConnectionManager::add(new MysqlConnectionString($options));

        $gen = new DBGenerator();
        $gen->generateFromModel(ConnectionManager::getConnection(), $this->path, $this->out);

        $this->log('SQL write into file "' . $this->out . '"');
        //print($this->message);
    }
}
