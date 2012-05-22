<?php
/**
 * ORMRelationHierarchy
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
 * @category   System
 * @package    ORM
 * @subpackage Relation
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    SVN: $Rev: 27 $
 * @link       http://bazalt.org.ua/
 */
 
 /**
 * ORMRelationHierarchy описує звязок моделі саму на себе
 * за допомогою двох полів. Наприклад id та parent_id.
 *
 * @category   System
 * @package    ORM
 * @subpackage Relation
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    Release: $Rev: 27 $
 * @link       http://bazalt.org.ua/
 */
class ORMRelationHierarchy extends ORM_Relation_Abstract
{
    /**
     * Назва проміжної моделі
     *
     * @var string
     */    
    public $refTable = null;
    
    /**
     * Constructor
     *
     * @param string $name      Назва моделі до якої іде звязок
     * @param string $column    Назва поля (стовпця) моделі від якої йде звязок
     * @param string $refColumn Назва поля (стовпця) моделі до якої йде звязок     
     */
    public function __construct($name, $column, $refColumn)
    {
        $this->name = $name;
        $this->column = $column;
        $this->refColumn = $refColumn;
    }

    /**
     * Викликається при зверненні до об'єкту зв'язку і 
     * повертає масив дочірніх відносно до поточного обєктів
     *
     * @return array
     */
    public function get()
    {
        return $this->getQuery()->fetchAll($this->name);
    }
    
    /**
     * Генерує запит для вибірки звязаних обєктів
     *
     * @return SelectQueryBuilder
     */
    public function getQuery()
    {        
        if (!isset($this->baseObject->{$this->column})) {
            return null;
        }
        $refVal = $this->baseObject->{$this->column};
        $q = ORMQuery::selectBuilder()
            ->from($this->name.' ft')
            ->orWhere('ft.'.$this->refColumn.' = ?', $refVal);
        return $q;
    }

    /**
     * Визначає чи буде повертати обєкт звязку 
     * як результат звернення один обєкт чи колекцію
     *
     * @return bool
     */    
    public function isManyResult()
    {
        return true;
    }

    /**
     * Генерує Sql скрипт для звязку @deprecated
     *
     * @param ORMRecord $model Модель до якої йде звязок
     * 
     * @return string
     */
    public function generateSql( $model )
    {
    }
}