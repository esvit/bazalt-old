<?php

using('Framework.Vendors.Sphinx');

class Sphinx_Config extends Object implements IWebConfig
{
    protected static $instance = null;

    protected $client = null;

    protected $host = 'localhost';
    
    protected $port = 9312;

    protected $indexName = null;

    protected $logFile = '/dev/null';

    protected $logQuery = '/dev/null';
    
    protected $indexPath = null;
    
    protected $pidFile = null;
    
    protected $readTimeout = 5;
    
    protected $memLimit = 256;
    
    protected $maxChildren = 5;
    
    protected $maxMatches = 1000;
    
    protected $xmlPipeFile = null;

    public function __construct()
    {
        parent::__construct();
    }

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    private static $allowAttributes = array(
        'host',
        'port',
        'indexName',
        'logFile',
        'indexPath',
        'pidFile',
        'logQuery',
        'readTimeout',
        'memLimit',
        'maxChildren',
        'maxMatches',
        'xmlPipeFile'
    );
    
    public function getClient()
    {
        if($this->client == null) {
            $this->client = new SphinxClient();
            $this->client->SetServer($this->host, $this->port);
            $this->client->SetConnectTimeout(1000);
            $this->client->SetArrayResult(true);
            if (!$this->client->open()) {
                throw new Sphinx_Exception($this->client->getLastError());
            }
        }
        return $this->client;
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
    
    public function reIndex()
    {
        return shell_exec('/usr/local/bin/indexer --all --rotate 2>&1');
    }

    public function printConfig($indexes = array())
    {
        $vars = array(
            'log_file' => $this->logFile,
            'query_log' => $this->logQuery,
            'index_path' => $this->indexPath,
            'searchd_pid' => $this->pidFile,
            'read_timeout' => $this->readTimeout,
            'mem_limit' => $this->memLimit,
            'max_children' => $this->maxChildren,
            'max_matches' => $this->maxMatches,
            'xml_pipe_file' => $this->xmlPipeFile
        );
        extract($vars);

        include dirname(__FILE__) . '/sphinx.conf.php';
    }
}