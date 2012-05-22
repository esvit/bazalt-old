<?php
/**
 * Драйвер кешу, який зберігає кожен ключ кешу в масив $items
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
 * @category   Cache
 * @package    BAZALT/Cache
 * @subpackage Adapters
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    SVN: $Rev: 20 $
 * @link       http://bazalt.org.ua/
 */

 
 
/**
 * Драйвер кешу, який зберігає кожен ключ кешу в змінну, такий кеш працює тільки на період 1 запиту до аплікейшина
 *
 * @category   Cache
 * @package    BAZALT/Cache
 * @subpackage Adapters
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    Release: $Rev: 20 $
 * @link       http://bazalt.org.ua/
 */
class Cache_Memory_Adapter extends Cache_AbstractAdapter
{
    /**
     * Масив данних кешу. Формат ключ кешу => значення
     *
     * @var array
     */  
    protected $items = array();
    
    /**
     * Масив тагів
     *
     * @var array
     */
    protected $tags = array();
    
    /**
     * Constructor
     *
     * @param Cache $cache Обєкт кешу
     * @param array $options Додаткові параметри (в цьому драйвері не використовуються)
     */     
    public function __construct($cache, $options = array())
    {
        $this->active = true;
        parent::__construct($cache, $options);
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
        $this->items[$id] = $data;
        $this->tags[$id] = $tags;
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
        if(isset($this->items[$id])) {
            return $this->items[$id];
        }
        return false;
    }
    
    /**
     * Видаляє данні з кешу по ключу
     *
     * @param string $id Ключ
     *
     * @return void
     */
    public function remove($id)
    {
        unset($this->items[$id]);
        unset($this->tags[$id]);
    }
    
    /**
     * Видаляє данні з кешу по тагу
     *
     * @param string $tag Таг
     *
     * @return void
     */
    public function removeByTag($tag, $salt = null)
    {
        foreach($this->tags as $id => $tags) {
            if(in_array($tag, $tags) && ($salt != null && in_array($salt, $tags))) {
                $this->remove($id);
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
        $this->items = array();
        $this->tags = array();
    }
}