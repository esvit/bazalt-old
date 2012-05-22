<?php
/**
 * Абстрактиний драйвер кешу
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
 * @category   Session
 * @package    BAZALT/Session
 * @subpackage Drivers
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    SVN: $Rev: 20 $
 * @link       http://bazalt.org.ua/
 */

 
 
/**
 * Абстрактиний адаптер кешу
 *
 * @category   Cache
 * @package    BAZALT/Cache
 * @subpackage Adapters
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    Release: $Rev: 20 $
 * @link       http://bazalt.org.ua/
 */
abstract class Cache_AbstractAdapter extends Object implements IAbstractAdapter
{
    protected $active = false;

    /**
     * Constructor
     *
     * @param Cache $cache Обєкт кешу
     * @param array $options Додаткові параметри (в цьому драйвері не використовуються)
     */  
    public function __construct($cache, $options = array())
    {
        parent::__construct();
    }
    
    /**
     * Повертає значеня по ключу 
     *
     * @param string $id Ключ
     *
     * @return mixed
     */
    abstract function get($id);
    
    
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
    abstract function set($id, $data, $lifeTime, $tags = array());
    
    /**
     * Видаляє данні з кешу по тагу
     *
     * @param string $tag  Таг
     * @param string $salt Salt as tag
     *
     * @return void
     */
    abstract function removeByTag($tag, $salt = null);
    
    /**
     * Видаляє данні з кешу по тагах
     *
     * @param array $tags Масив тагів
     * @param array $salt Salt as tag
     *
     * @return void
     */
    abstract function removeByTags($tags, $salt = null);

    abstract function clearCache();

    public function isActive()
    {
        return $this->active;
    }
}