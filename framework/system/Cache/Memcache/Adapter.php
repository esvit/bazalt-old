<?php

class Cache_Memcache_Adapter extends Cache_AbstractAdapter
{
    protected $host = 'localhost';

    protected $port = 11211;

    protected $memcache;

    /**
     * Constructor
     *
     * @param Cache $cache Обєкт кешу
     * @param array $options Додаткові параметри (в цьому драйвері не використовуються)
     */     
    public function __construct($cache, $options = array())
    {
        parent::__construct($cache, $options);

        if (!extension_loaded('memcache') && !extension_loaded('memcached')) {
            //throw new Exception('Extension memcache or memcached is not loaded');
            $this->active = false;
            return;
        }

        if (isset($options['host'])) {
            $this->host = $options['host'];
        }

        if (isset($options['port'])) {
            $this->port = $options['port'];
        }

        if (extension_loaded('memcached')) {
            $this->memcache = new Memcached();
            $this->getLogger()->info('Use extension Memcached');
        } else if (extension_loaded('memcache')) {
            $this->memcache = new Memcache();
            $this->getLogger()->info('Use extension Memcache');
        }

        if ($this->memcache->addServer($this->host, $this->port) === false || @$this->memcache->getStats() === false) {
            // throw new Exception('Could not connect to Memcache server ' . $this->host . ':' . $this->port);
            $this->active = false;
            return;
        }

        $this->active = true;
    }

    /**
     * Встановлює данні в кеш
     *
     * @param string  $id Ключ
     * @param mixed   $data Дані
     * @param integer $lifeTime Час життя кешу в секундах
     * @param array   $tags Таги кешу
     *
     * @return void
     */
    public function set($id, $data, $lifeTime, $tags = array())
    {
        // if (empty($data) || $data === false) {
            // $data = 'array(null)';
        // }
        if(count($tags) > 0) {
            foreach($tags as $tag) {
                if($this->addToTag($id, $tag) === false) {
                    return;
                    // echo('Cannot set cache tag ' . $tag . ' for id ' . $id."\n");
                    // $this->getLogger()->err('Cannot set cache tag ' . $tag . ' for id ' . $id);
                }
            }
        }
        $result = $this->_set($id, $data, $lifeTime);
        if (!$result) {
            return;
            // echo('Cannot set cache ' . $id . ' data ' . print_r($data, true)."\n");
            // $this->getLogger()->err('Cannot set cache ' . $id . ' data ' . print_r($data, true));
            // throw new Cache_Memcache_Exception($this->memcache);
        }
    }

    private function _set($id, $data, $lifeTime)
    {
        if (extension_loaded('memcached')) {
            if($lifeTime != 0) {
                $lifeTime = time() + $lifeTime;
            }
            if (!($result = $this->memcache->replace($id, $data, $lifeTime))) {
                $result = $this->memcache->set($id, $data, $lifeTime);
            }
        } else if (extension_loaded('memcache')) {
            if (!($result = $this->memcache->replace($id, $data, false, $lifeTime))) {
                $result = $this->memcache->set($id, $data, false, $lifeTime);
            }
        }
        return $result;
    }
    
    protected function addToTag($id, $tagId)
    {
        $tags = false;
        if($this->isExists($tagId)) {
            $tags = $this->get($tagId);
        }
        if($tags === false) {
            $tags = array($id => $id);
        } else {
            $tags[$id] = $id;
        }
        return $this->_set($tagId, $tags, 0);
    }
    
    /**
     * Перевіряє чи існує файл для ключа $id
     *
     * @param string  $id Ключ
     *
     * @return bool
     */
    public function isExists($id)
    {
        return ($this->get($id) !== false);
    }

    /**
     * Повертає значеня по ключу 
     *
     * @param string $id Ключ
     *
     * @return mixed
     */
    public function get($id) 
    {
        return $this->memcache->get($id);
    }
    
    /**
     * Видаляє данні з кешу по тагу
     *
     * @param string $tag Таг
     *
     * @return void
     */
    public function removeByTag($tagId, $salt = null)
    {
        if($this->isExists($tagId)) {
            $tagIds = $this->get($tagId);
            if($tagIds === false) {
                return;
            }
            if($salt != null) {
                $saltIds = $this->get($salt);
                $tagIds = array_intersect($tagIds, $saltIds);
            }
            foreach($tagIds as $id) {
                if (!$this->memcache->delete($id)) {
                    //$this->getLogger()->err('Cannot delete cache ' . $id);
                    $this->memcache->set($id, false);
                }
            }
            if (!$this->memcache->delete($tagId)) {
                $this->getLogger()->err('Cannot delete cache ' . $tagId);
                $this->memcache->set($tagId, false);
            }
        }
    }
    
    /**
     * Видаляє данні з кешу по тагах
     *
     * @param array $tags Масив тагів
     *
     * @return void
     */
    public function removeByTags($tags, $salt = null)
    {
        foreach($tags as $tag) {
            $this->removeByTag($tag, $salt);
        }
    }
    
    public function clearCache()
    {
        $this->memcache->flush();
    }
}