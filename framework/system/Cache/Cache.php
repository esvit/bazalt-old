<?php
/**
 * Cache
 *
 * PHP versions 5
 *
 * LICENSE:
 * 
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Cache
 * @author     Vitalii Savchuk <esvit666@gmail.com>
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    $Revision: 145 $
 * @link       http://www.bazalt.org.ua/
 */

using('Framework.System.Config');

/**
 *
 *
 * @category   Core
 * @package    BAZALT
 * @subpackage Cache
 * @author     Vitalii Savchuk <esvit666@gmail.com>
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    $Rev: 145 $
 * @link       http://www.bazalt.org.ua/
 */
class Cache extends Config_Adaptee
{
    protected static $instance = null;

    protected static $enabled = true;

    const TIME_DAY = 86400;
    
    const TIME_HOUR = 3600;
    
    const DEFAULT_LIFE_TIME = 86400;//deprecated
    
    protected $adapterClass = 'Cache_Memory_Adapter';//default adapter
    
    protected $defaultLifeTime = 86400;
    
    protected $salt = 'bazalt_';

    public static function disable()
    {
        self::$enabled = false;
    }

    public static function enable()
    {
        self::$enabled = true;
    }

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }
    
    public function initCache($adapter, $options)
    {
        $adapter = new $adapter($this, $options);
        if($adapter->isActive()) {
            $this->setAdapter($adapter);
        }

        return $this;
    }
    
    /**
     * Get/Set salt for cache keys
     *
     * @param string $salt Salt
     */
    public function salt($salt = null)
    {
        if ($salt != null) {
            $this->salt = $salt;
            return $this;
        }
        return $this->salt;
    }
    
    /**
     * Get/Set default life time for cache
     *
     * @param string $salt Salt
     */
    public function defaultLifeTime($defaultLifeTime = null)
    {
        if ($defaultLifeTime != null) {
            $this->defaultLifeTime = $defaultLifeTime;
            return $this;
        }
        return $this->defaultLifeTime;
    }
    
    /**
     * Test if a cache record exists for the passed id
     *
     * @param string $id cache id
     * @return mixed  Returns either the cached data or false
     */
    public function getCache($id)
    {
        if (!defined('CACHE') || !CACHE === true || !self::$enabled || !$this->getAdapter()->isActive()) {
            return false;
        }
        $key = md5($this->salt . $id);
        $result = $this->getAdapter()->get($key);

        $this->getLogger()->info('Get cache by key "' . $key . '" return ' . (($result !== false) ? 'non empty result' : 'empty result'));
        return $result;
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
    public function setCache($id, $data, $lifeTime = false, $tags = array())
    {
        if (!defined('CACHE') || !CACHE === true || !self::$enabled || !$this->getAdapter()->isActive()) {
            return false;
        }
        if ($lifeTime === false) {
            $lifeTime = $this->defaultLifeTime();
        }
        $tags = array_unique(array_values($tags));
        $key = md5($this->salt . $id);
        $tags []= $this->salt;
        $this->getAdapter()->set($key, $data, $lifeTime, $tags);
    }
    
    /**
     * Видаляє данні з кешу по тагу
     *
     * @param string $tag Таг
     *
     * @return void
     */
    public function removeByTag($tag)
    {
        if (!defined('CACHE') || !CACHE === true || !self::$enabled || !$this->getAdapter()->isActive()) {
            return false;
        }
        $this->getAdapter()->removeByTag($tag, $this->salt);
    }
    
    /**
     * Видаляє данні з кешу по тагах
     *
     * @param array $tags Масив тагів
     *
     * @return void
     */
    public function removeByTags($tags)
    {
        if (!defined('CACHE') || !CACHE === true || !self::$enabled || !$this->getAdapter()->isActive()) {
            return false;
        }
        $this->getAdapter()->removeByTags($tags, $this->salt);
    }

    public function clearCache()
    {
        if (!defined('CACHE') || !CACHE === true || !self::$enabled || !$this->getAdapter()->isActive()) {
            return false;
        }
        $this->getAdapter()->clearCache();
    }
}