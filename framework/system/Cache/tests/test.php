<?php

// sleep(60);exit(0);

// $count = count(explode("\n", trim(shell_exec('ps -a | grep php'))));
// print_r(count(explode("\n", trim(shell_exec('ps -a | grep php')))) - 1);
// exit;

error_reporting(E_ALL & ~E_DEPRECATED | E_STRICT);
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('report_memleaks', 'on');

define('DEBUG', true);
define('CACHE', true);
define('SITE_DIR', realpath(dirname(__FILE__).'/../../../../')); // no trailing slash
define('CACHE_DIR', SITE_DIR . '/tmp/cache');
// print SITE_DIR;exit;
define('ERROR_LOG_FILE', SITE_DIR . '/fixme.log');

/**
 * Include BAZALT framework
 *
 * .htaccess or httpd.conf, etc...
 * - SetEnv BAZALT_FRAMEWORK /path/to/framework
 */
require_once (is_dir(SITE_DIR . '/framework') ? (SITE_DIR . '/framework') : getenv('BAZALT_FRAMEWORK')) . '/Core/include.inc';

using('Framework.System.Cache');

Cache::Singleton()->salt('salt'); // cache salt, for memcache
Cache::Singleton()->initCache('Cache_Memcache_Adapter', array('host' => 'localhost', 'port' => 11211));



class CacheTest
{
    public $keys = array(
        'key1',
        'key2'
    );
    
    public function run()
    {
        foreach($this->keys as $key) {
            $this->_delete($key);
        }
    
        for($i=0;$i<100;$i++) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                 die('could not fork');
            } else if ($pid) {
                 // we are the parent
                 pcntl_wait($status); //Protect against Zombie children
                 $this->read();
                 // echo 'read'."\n";
            } else {
                 $fl = rand(0, 100)%2 == 0;
                 // we are the child
                 if($fl) {
                    $this->clear();
                    // echo $this->fl.'|'.getmypid().' - ';
                    // exit('clear'."\n");
                    exit;
                 } else {
                    $this->write();
                    // echo $this->fl.'|'.getmypid().' - ';
                    // exit('write'."\n");
                    exit;
                 }
            }
        }
    }
    
    private function _set($key, $val)
    {
        file_put_contents('/tmp/'.$key, $val, LOCK_EX);
    }

    private function _get($key)
    {
        return file_exists('/tmp/'.$key) ?  trim(file_get_contents('/tmp/'.$key)) : '';
    }

    private function _delete($key)
    {
        return file_exists('/tmp/'.$key) ?  unlink('/tmp/'.$key) : false;
    }

    public function clear()
    {
        Cache::Singleton()->removeByTag('test');
        foreach($this->keys as $key) {
            $this->_delete($key);
        }
    }

    public function write()
    {
        foreach($this->keys as $key) {
            $val = mt_rand(1, 10000).microtime().'';
            Cache::Singleton()->setCache($key, $val, false, array('test'));
            $this->_set($key, $val);
        }
    }
    
    public function read()
    {
        foreach($this->keys as $key) {
            $v1 = $this->_get($key);
            $v2 = Cache::Singleton()->getCache($key);
            $v2 = Cache::Singleton()->getCache($key);
            if($v1 != $v2) {
                var_dump($v1);
                var_dump($v2);
                exit('Fail - '.$v1 .' != '. $v2."\n");
            }
        }
    }
}

print get_class(Cache::Singleton()->getAdapter())."\n";
$c = new CacheTest();
$c->run();