<?php
/**
 * NestedSet.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM_Relation_NestedSet
 * Реалізація NestedSet {@link http://en.wikipedia.org/wiki/Nested_set_model}.
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class ORM_Relation_NestedSet extends ORM_Relation_Abstract
{
    const LEFT_FIELDNAME = 'lft';

    const RIGHT_FIELDNAME = 'rgt';

    const DEPTH_FIELDNAME = 'depth';

    protected $options = array();

    protected static $error = array();

    /**
     * Constructor
     *
     * @param string $name             Назва моделі до якої іде звязок
     * @param string $column           Назва поля (стовпця) моделі від якої йде звязок
     * @param string $refColumn        Назва поля (стовпця) моделі до якої йде звязок     
     * @param string $additionalParams Масив додаткових параметрів, 
     *                                 які будуть враховуватись при вибірках по звязку
     */
    public function __construct($name, $column, $refColumn = null, $additionalParams = null)
    {
        $this->name = $name;
        $this->column = $column;
        $this->refColumn = ($refColumn == null) ? $column : $refColumn;
        $this->additionalParams = $additionalParams;
    }

    public function initForModel($model)
    {
        $model->hasColumn(self::LEFT_FIELDNAME, 'UN:int(10)');
        $model->hasColumn(self::RIGHT_FIELDNAME, 'UN:int(10)');
        $model->hasColumn(self::DEPTH_FIELDNAME, 'UN:int(10)');
    }

    public static function getLastErrors()
    {
        return self::$error;
    }

    /**
     * Викликається при зверненні до об'єкту зв'язку і 
     * повертає масив дочірніх відносно до поточного обєктів
     *
     * @return array
     */
    public function get($depth = null)
    {
        $q = $this->getQuery($depth);
        if ($q == null) {
            return null;
        }
        return self::makeTree($q->fetchAll());
    }

    protected static function getRecordById($id, $className = null)
    {
        if (!is_numeric($id)) {
            throw new InvalidArgumentException();
        }

        $field = ORM_Record::getAutoIncrementColumn($className);
        $q = ORM::select($className . ' f')
                ->andWhere('f.'.$field->name().' = ?', $id)
                ->limit(1)
                ->noCache();

        return $q->fetch($className);
    }

    /**
     * Генерує запит для вибірки звязаних обєктів
     *
     * @return SelectQueryBuilder
     */
    public function getQuery($depth = null)
    {
        if (!isset($this->baseObject->{self::LEFT_FIELDNAME}) || !isset($this->baseObject->{self::RIGHT_FIELDNAME})) {
            return null;
        }
        // need! because update query can update field values
        $this->baseObject = self::getRecordById((int)$this->baseObject->id, get_class($this->baseObject));

        $left = $this->baseObject->{self::LEFT_FIELDNAME} + 1;
        $right = $this->baseObject->{self::RIGHT_FIELDNAME} - 1;
        $q = ORM::select($this->name . ' ft')
                ->where('ft.' . self::LEFT_FIELDNAME . ' BETWEEN ? AND ?', array($left, $right))
                ->andWhere('ft.'.$this->column . ' = ?', $this->baseObject->{$this->column})
                ->orderBy('ft.' . self::LEFT_FIELDNAME . ' ASC');

        if ($depth != null) {
            $q->andWhere(self::DEPTH_FIELDNAME . ' <= ?', $this->baseObject->depth + $depth);
        }
        $this->applyAddParams($q);
        return $q;
    }

    public function add($element)
    {
        ORM::begin();
        // need! because update query can update field values
        $this->baseObject = self::getRecordById((int)$this->baseObject->id, get_class($this->baseObject));

        $right = $this->baseObject->{self::RIGHT_FIELDNAME};

        if (empty($this->baseObject->{$this->column})) {
            throw new Exception('Category column "' . $this->column . '" empty, need for nestedset');
        }
        // зсуває елементи дерева щоб поставити елемент
        $q = ORM::update($this->name)
                ->set('lft = IF(lft > ' . $right . ', lft + 2, lft)')
                ->set('rgt = rgt + 2')
                ->where('rgt > ?', $right)
                ->andWhere($this->column . ' = ' . $this->baseObject->{$this->column});
        $q->exec();

        $element->lft = $right;
        $element->rgt = $right + 1;
        $element->{self::DEPTH_FIELDNAME} = $this->baseObject->{self::DEPTH_FIELDNAME} + 1;
        $element->save();

        $this->baseObject->rgt = $right + 2;
        $this->baseObject->save();

        if (isset($element->Childrens) && is_array($element->Childrens)) {
            $cls = get_class($element);
            foreach ($element->Childrens as $child) {
                if (get_class($child) == $cls) {
                    $element->Elements->add($child);
                }
            }
        }

        if (!$this->analyze()) {
            $this->getLogger()->err('Nested set has errors. Rollback');
            ORM::rollBack();
            return false;
        }
        ORM::commit();
        // need! because update query can update field values
        $this->baseObject = self::getRecordById((int)$this->baseObject->id, get_class($this->baseObject));
        return $element;
    }

    public function insertBefore($element)
    {
        // need! because update query can update field values
        $this->baseObject = self::getRecordById((int)$this->baseObject->id, get_class($this->baseObject));

        $beforeItem = $this->baseObject;

        if (!$beforeItem) {
            throw new Exception('Invalid element for insert');
        }

        $left = $beforeItem->{self::LEFT_FIELDNAME};

        $q = ORM::update($this->name)
                ->set('lft = IF(lft > ' . $left . ', lft + 2, lft)')
                ->set('rgt = rgt + 2')
                ->where(self::RIGHT_FIELDNAME . ' > ?', $left)
                ->andWhere($this->column . ' = '. $this->baseObject->{$this->column});
        $q->exec();

        // insert element before element at position $pos
        $element->{self::LEFT_FIELDNAME} = $left;
        $element->{self::RIGHT_FIELDNAME} = $left + 1;
        $element->{self::DEPTH_FIELDNAME} = $this->baseObject->{self::DEPTH_FIELDNAME};
        $element->save();

        // move element at position $pos to down
        $beforeItem->{self::LEFT_FIELDNAME} = $beforeItem->{self::LEFT_FIELDNAME} + 2;
        $beforeItem->{self::RIGHT_FIELDNAME} = $beforeItem->{self::RIGHT_FIELDNAME} + 2;
        $beforeItem->save();

        if (isset($element->Childrens) && is_array($element->Childrens)) {
            $cls = get_class($element);
            foreach ($element->Childrens as $child) {
                if (get_class($child) == $cls) {
                    $element->Elements->add($child);
                }
            }
        }
    }

    public function moveAfter($element, $clone = false)
    {
        // need! because update query can update field values
        $afterEl = $this->baseObject = self::getRecordById((int)$this->baseObject->id, get_class($this->baseObject));
        
        $step = $element->{self::RIGHT_FIELDNAME} - $element->{self::LEFT_FIELDNAME} + 1;
        $lft = $element->{self::LEFT_FIELDNAME};
        $rgt = $element->{self::RIGHT_FIELDNAME};
        $id = $element->id;

        ORM::begin();

        // убираем ветку
        $q = ORM::update($this->name)
                ->set(self::LEFT_FIELDNAME . ' =- ' . self::LEFT_FIELDNAME)
                ->set(self::RIGHT_FIELDNAME . ' =- ' . self::RIGHT_FIELDNAME)
                ->where(self::LEFT_FIELDNAME . ' >= ' . $lft)
                ->andWhere(self::LEFT_FIELDNAME . ' < ' . $rgt)
                ->andWhere($this->column . ' = '. $this->baseObject->{$this->column});
        $q->exec();

        $qUpdateLeft = ORM::update($this->name);
        $qUpdateRight = ORM::update($this->name);

        if ($afterEl->{self::RIGHT_FIELDNAME} > $element->{self::LEFT_FIELDNAME}) {
            $distance = $afterEl->{self::RIGHT_FIELDNAME} - $rgt;
            $step *= -1; // substract step

            // условия обновление левой границы
            $qUpdateLeft->where(self::LEFT_FIELDNAME . ' >= ?', $element->{self::LEFT_FIELDNAME})
                        ->andWhere(self::LEFT_FIELDNAME . ' < ?', $afterEl->{self::RIGHT_FIELDNAME});

            // условия обновление правой границы
            $qUpdateRight->where(self::RIGHT_FIELDNAME . ' > ?', $element->{self::LEFT_FIELDNAME})
                         ->andWhere(self::RIGHT_FIELDNAME . ' <= ?', $afterEl->{self::RIGHT_FIELDNAME});
        } else {
            $distance = -($rgt - $afterEl->{self::RIGHT_FIELDNAME} - $step); // substract distance

            // условия обновление левой границы
            $qUpdateLeft->where(self::LEFT_FIELDNAME . ' > ?', $afterEl->{self::RIGHT_FIELDNAME})
                        ->andWhere(self::LEFT_FIELDNAME . ' <= ?', $element->{self::RIGHT_FIELDNAME});

            // условия обновление правой границы
            $qUpdateRight->where(self::RIGHT_FIELDNAME . ' > ?', $afterEl->{self::RIGHT_FIELDNAME})
                         ->andWhere(self::RIGHT_FIELDNAME . ' < ?', $element->{self::RIGHT_FIELDNAME});
        }

        // обновляем левую границу промежуточных елементов
        $qUpdateLeft->set(self::LEFT_FIELDNAME . ' = ' . self::LEFT_FIELDNAME . ' + ' . $step)
                    ->andWhere($this->column . ' = '. $this->baseObject->{$this->column})
                    ->exec();

        // обновляем правую границу промежуточных елементов
        $qUpdateRight->set(self::RIGHT_FIELDNAME . ' = ' . self::RIGHT_FIELDNAME . ' + ' . $step)
                     ->andWhere($this->column . ' = '. $this->baseObject->{$this->column})
                     ->exec();

        // возвращаем ветку после елемента
        $q = ORM::update($this->name)
                ->set(self::LEFT_FIELDNAME . ' = -' . self::LEFT_FIELDNAME . ' + ' . $distance)
                ->set(self::RIGHT_FIELDNAME . ' = -' . self::RIGHT_FIELDNAME . ' + ' . $distance)
                ->set(self::DEPTH_FIELDNAME . ' = ' . self::DEPTH_FIELDNAME . ' - ' . $element->{self::DEPTH_FIELDNAME} . ' + ' . $afterEl->{self::DEPTH_FIELDNAME})
                ->where(self::LEFT_FIELDNAME . ' <= ' . -$lft)
                ->andWhere(self::LEFT_FIELDNAME . ' >= ' . -$rgt)
                ->andWhere($this->column . ' = '. $this->baseObject->{$this->column});
        $q->exec();

        if (!$this->analyze()) {
            $this->getLogger()->err('Nested set has errors. Rollback');
            ORM::rollBack();
            return false;
        }
        ORM::commit();
        // need! because update query can update field values
        $this->baseObject = self::getRecordById((int)$this->baseObject->id, get_class($this->baseObject));
        return true;
    }
    
    public function moveIn($element, $clone = false)
    {
        // need! because update query can update field values
        $parent = $this->baseObject = self::getRecordById((int)$this->baseObject->id, get_class($this->baseObject));
        
        ORM::begin();

        $step = $element->{self::RIGHT_FIELDNAME} - $element->{self::LEFT_FIELDNAME} + 1;
        $lft = $element->{self::LEFT_FIELDNAME};
        $rgt = $element->{self::RIGHT_FIELDNAME};
        $id = $element->id;
        $distance = $lft - $parent->{self::LEFT_FIELDNAME} - 1;

        // UPDATE %s SET lft=-lft, rgt=-rgt WHERE lft>=%d AND lft<=%d;
        $q = ORM::update($this->name)
                ->set(self::LEFT_FIELDNAME . ' =- ' . self::LEFT_FIELDNAME)
                ->set(self::RIGHT_FIELDNAME . ' =- ' . self::RIGHT_FIELDNAME)
                ->where(self::LEFT_FIELDNAME . ' >= ?', $lft)
                ->andWhere(self::LEFT_FIELDNAME . ' <= ?', $rgt)
                ->andWhere($this->column . ' = '. $this->baseObject->{$this->column});
        $q->exec();
        
        if ($parent->{self::LEFT_FIELDNAME} > $element->{self::LEFT_FIELDNAME}) {
            $distance = $parent->{self::LEFT_FIELDNAME} - $rgt;
            $step *= -1;
            //UPDATE %s SET lft=lft+%d WHERE lft>%d AND lft<%d;
            $q = ORM::update($this->name)
                    ->set(self::LEFT_FIELDNAME . ' = ' . self::LEFT_FIELDNAME . '+' . $step)
                    ->where(self::LEFT_FIELDNAME . ' > ?', $lft)
                    ->andWhere(self::LEFT_FIELDNAME . ' <= ?', $parent->{self::LEFT_FIELDNAME})
                    ->andWhere($this->column . ' = '. $this->baseObject->{$this->column});
            $q->exec();

            $q = ORM::update($this->name)
                    ->set(self::RIGHT_FIELDNAME . ' = ' . self::RIGHT_FIELDNAME . '+' . $step)
                    ->where(self::RIGHT_FIELDNAME . ' > '. $rgt)
                    ->andWhere(self::RIGHT_FIELDNAME . ' <= '. $parent->{self::LEFT_FIELDNAME})
                    ->andWhere($this->column . ' = '. $this->baseObject->{$this->column});
            $q->exec();
        } else {
            $distance *= -1;
            $q = ORM::update($this->name)
                    ->set(self::LEFT_FIELDNAME.' = '.self::LEFT_FIELDNAME.'+'. $step)
                    ->where(self::LEFT_FIELDNAME . ' > ?', $parent->{self::LEFT_FIELDNAME})
                    ->andWhere(self::LEFT_FIELDNAME . ' < ?', $lft)
                    ->andWhere($this->column . ' = '. $this->baseObject->{$this->column});
            $q->exec();

            //UPDATE %s SET rgt=rgt+%d WHERE rgt>%d AND rgt<%d;
            $q = ORM::update($this->name)
                    ->set(self::RIGHT_FIELDNAME.' = '.self::RIGHT_FIELDNAME.'+'. $step)
                    ->where(self::RIGHT_FIELDNAME . ' > ' . $parent->{self::LEFT_FIELDNAME})
                    ->andWhere(self::RIGHT_FIELDNAME . ' < ' . $lft)
                    ->andWhere($this->column . ' = '. $this->baseObject->{$this->column});
            $q->exec();
        }

        //UPDATE %s SET lft=-lft-%d, rgt=-rgt-%d WHERE lft<=-%d AND lft>=-%d;
        $q = ORM::update($this->name)
                ->set(self::LEFT_FIELDNAME . ' = -' . self::LEFT_FIELDNAME . ' + ' . $distance)
                ->set(self::RIGHT_FIELDNAME . ' = -' . self::RIGHT_FIELDNAME . ' + ' . $distance)
                ->set(self::DEPTH_FIELDNAME . ' = ' . self::DEPTH_FIELDNAME . ' - ' . $element->{self::DEPTH_FIELDNAME} . ' + 1 + ' . $parent->{self::DEPTH_FIELDNAME})
                ->where(self::LEFT_FIELDNAME . ' <= ' . -$lft)
                ->andWhere(self::LEFT_FIELDNAME . ' >= ' . -$rgt)
                ->andWhere($this->column . ' = ' . $this->baseObject->{$this->column});
        $q->exec();

        if (!$this->analyze()) {
            $this->getLogger()->err('Nested set has errors. Rollback');
            ORM::rollBack();
            return false;
        }
        ORM::commit();
        // need! because update query can update field values
        $this->baseObject = self::getRecordById((int)$this->baseObject->id, get_class($this->baseObject));
        return true;
    }

    public function analyze()
    {
        $report = array();

        $q = ORM::select($this->name . ' c1', 'c1.*')
                ->innerJoin($this->name . ' c2', array('lft', 'c2.lft AND c1.id != c2.id'))
                ->andWhere('c1.' . $this->column . ' = ?', $this->baseObject->{$this->column})
                ->andWhere('c2.' . $this->column . ' = ?', $this->baseObject->{$this->column})
                ->groupBy('c1.id')
                ->limit(2)
                ->noCache();

        $nodes = $q->fetchAll();
        if (!count($nodes)) {
            $this->getLogger()->err('Have same left number');
            $report[] = 'Have same left number';
            return false;
        }

        $q = ORM::select($this->name . ' c1', 'c1.*')
                ->innerJoin($this->name . ' c2', array('rgt', 'c2.rgt AND c1.id != c2.id'))
                ->andWhere('c1.' . $this->column . ' = ?', $this->baseObject->{$this->column})
                ->andWhere('c2.' . $this->column . ' = ?', $this->baseObject->{$this->column})
                ->groupBy('c1.id')
                ->limit(2)
                ->noCache();

        $nodes = $q->fetchAll();
        if (!count($nodes)) {
            $this->getLogger()->err('Have same right number');
            $report[] = 'Have same right number';
            return false;
        }

        $q = ORM::select($this->name)
                ->where('depth = ?', 0)
                ->andWhere($this->column . ' = '. $this->baseObject->{$this->column})
                ->noCache();

        $nodes = $q->fetchAll();
        if (!count($nodes)) {
            $this->getLogger()->err('No root node');
            $report[] = 'No root node';
            return false;
        } else if (count($nodes) > 1) {
            $this->getLogger()->err('More than one root node');
            $report[] = 'More than one root node';
            return false;
        } else if ($nodes[0]->{self::LEFT_FIELDNAME} != 1) {
            $this->getLogger()->err('Root node\'s left index is not 1');
            $report[] = 'Root node\'s left index is not 1';
            return false;
        }

        $q = ORM::select($this->name, 'MAX(' . self::RIGHT_FIELDNAME . ') AS `max_rgt`, COUNT(*) * 2 AS `count`')
                ->where($this->column . ' = '. $this->baseObject->{$this->column})
                ->noCache();

        $res = $q->fetch('stdClass');

        if ($res->max_rgt != $res->count) {
            $this->getLogger()->err('Right index does not match node count');
            $report[] = 'Right index does not match node count';
            return false;
        }

        $q = ORM::select($this->name . ' m1', 'm1.*')
                ->from($this->name . ' m2')
                ->where('m1.' . $this->column . ' = '. $this->baseObject->{$this->column})
                ->andWhere('m1.' . $this->column . ' = m2.' . $this->column)
                ->andWhere('(m1.lft = m2.lft OR m1.rgt = m2.rgt)')
                ->andWhere('m1.id != m2.id')
                ->noCache();

        if ($q->exec() > 1) {
            $this->getLogger()->err('You have repeat left or right columns');
            $report[] = 'You have repeat left or right columns';
            return false;
        }

        self::$error = $report;
        return true;//$report;
    }

    public function insertAfter($element)
    {
        // need! because update query can update field values
        $this->baseObject = self::getRecordById((int)$this->baseObject->id, get_class($this->baseObject));

        $afterItem = $this->baseObject;

        if (!$afterItem) {
            throw new Exception('Invalid element for insert');
        }

        $left = $afterItem->{self::RIGHT_FIELDNAME};

        $q = ORM::update($this->name)
                ->set('lft = IF(lft > ' . $left . ', lft + 2, lft)')
                ->set('rgt = rgt + 2')
                ->where(self::RIGHT_FIELDNAME . ' > ?', $left)
                ->andWhere($this->column . ' = '. $this->baseObject->{$this->column});
        $q->exec();

        // insert element before element at position $pos
        $element->{self::LEFT_FIELDNAME} = $left + 1;
        $element->{self::RIGHT_FIELDNAME} = $left + 2;
        $element->{self::DEPTH_FIELDNAME} = $this->baseObject->{self::DEPTH_FIELDNAME};
        $element->save();

        if (isset($element->Childrens) && is_array($element->Childrens)) {
            $cls = get_class($element);
            foreach ($element->Childrens as $child) {
                if (get_class($child) == $cls) {
                    $element->Elements->add($child);
                }
            }
        }
    }

    // replace code from prev function
    public function insert($element, $pos = 0)
    {
        if ($this->baseObject->id == $element->id) {
            throw new Exception('Cant insert element');
        }
        // need! because update query can update field values
        $this->baseObject = self::getRecordById((int)$this->baseObject->id, get_class($this->baseObject));

        $tree = $this->get();

        if (count($tree) == 0 && $pos == 0 || $pos >= count($tree)) {
            return $this->add($element);
        }

        $beforeItem = $tree[$pos];

        if (!$beforeItem) {
            throw new Exception('Invalid element for insert');
        }

        $left = $beforeItem->{self::LEFT_FIELDNAME};

        $q = ORM::update($this->name)
                ->set('lft = IF(lft > ' . $left . ', lft + 2, lft)')
                ->set('rgt = rgt + 2')
                ->where(self::RIGHT_FIELDNAME . ' > ?', $left)
                ->andWhere($this->column . ' = '. $this->baseObject->{$this->column});
        $q->exec();

        // insert element before element at position $pos
        $element->{self::LEFT_FIELDNAME} = $left;
        $element->{self::RIGHT_FIELDNAME} = $left + 1;
        $element->{self::DEPTH_FIELDNAME} = $this->baseObject->{self::DEPTH_FIELDNAME} + 1;
        $element->save();

        // move element at position $pos to down
        $beforeItem->{self::LEFT_FIELDNAME} = $beforeItem->{self::LEFT_FIELDNAME} + 2;
        $beforeItem->{self::RIGHT_FIELDNAME} = $beforeItem->{self::RIGHT_FIELDNAME} + 2;
        $beforeItem->save();

        if (isset($element->Childrens) && is_array($element->Childrens)) {
            $cls = get_class($element);
            foreach ($element->Childrens as $child) {
                if (get_class($child) == $cls) {
                    $element->Elements->add($child);
                }
            }
        }
        return true;
    }

    public function remove($elem, $onlyParent = false)
    {
        if (get_class($elem) != $this->name) {
            throw new Exception('Invlid object. Must be ' . $this->name);
        }
        
        // need! because update query can update field values
        $this->baseObject = self::getRecordById((int)$this->baseObject->id, get_class($this->baseObject));
    
        
        $left = $elem->{self::LEFT_FIELDNAME};
        $right = $elem->{self::RIGHT_FIELDNAME};
        if($left <= $this->baseObject->{self::LEFT_FIELDNAME} || $right >= $this->baseObject->{self::RIGHT_FIELDNAME}) {
            throw new Exception("Invlid parent object ($left,$right)");
        }

        if ($onlyParent) {
            $count = 2;
            $q = ORM::update($this->name)
                    ->set(self::LEFT_FIELDNAME . ' = ' . self::LEFT_FIELDNAME . ' - 1')
                    ->set(self::RIGHT_FIELDNAME . ' = ' . self::RIGHT_FIELDNAME . ' - 1')
                    ->where(self::LEFT_FIELDNAME . ' > ?', $left)
                    ->andWhere(self::RIGHT_FIELDNAME . ' < ?', $right)
                    ->andWhere($this->column . ' = ?', $this->baseObject->{$this->column});

            $q->exec();
            $elem->delete();
        } else {
            $count = $right - $left + 1;
            $q = ORM::delete($this->name)
                    ->where(self::LEFT_FIELDNAME . ' >= ?', $left)
                    ->andWhere(self::RIGHT_FIELDNAME . ' <= ?', $right)
                    ->andWhere($this->column . ' = ?', $this->baseObject->{$this->column});
            $q->exec();
        }

        $q = ORM::update($this->name)
                ->set('lft = IF(lft > ' . $left . ', lft - ' . $count . ', lft)')
                ->set('rgt = rgt - ' . $count)
                ->where('rgt > ?', $right)
                ->andWhere($this->column . ' = ?', $this->baseObject->{$this->column});
        $q->exec();

        $this->baseObject->{self::RIGHT_FIELDNAME} = $this->baseObject->{self::RIGHT_FIELDNAME} - $count;
        $this->baseObject->save();
    }

    public function removeAll()
    {
        $left = $this->baseObject->{self::LEFT_FIELDNAME};
        $right = $this->baseObject->{self::RIGHT_FIELDNAME};

        $count = $right - $left + 1;
        $q = ORM::delete($this->name)
                ->where(self::LEFT_FIELDNAME . ' > ?', $left)
                ->andWhere(self::RIGHT_FIELDNAME . ' < ?', $right)
                ->andWhere($this->column . ' = ?', $this->baseObject->{$this->refColumn});
        $q->exec();

        $q = ORM::update($this->name)
                ->set('lft = IF(lft > ' . $left . ', lft - ' . $count . ', lft)')
                ->set('rgt = rgt - ' . $count)
                ->where('rgt >= ?', $right)
                ->andWhere($this->column . ' = ?', $this->baseObject->{$this->refColumn});
        $q->exec();

        $this->baseObject->{self::RIGHT_FIELDNAME} = $left + 1;
        $this->baseObject->save();
    }

    public function getChildrenCount()
    {
        $left = $this->baseObject->{self::LEFT_FIELDNAME};
        $right = $this->baseObject->{self::RIGHT_FIELDNAME};
        return ($right - $left - 1) / 2;
    }

    public function getParent()
    {
        $left = $this->baseObject->{self::LEFT_FIELDNAME};
        $right = $this->baseObject->{self::RIGHT_FIELDNAME};
        $q = ORM::select($this->name . ' ft')
                ->where('ft.' . self::LEFT_FIELDNAME . ' < ?', $left)
                ->andWhere('ft.' . self::RIGHT_FIELDNAME . ' > ?', $right)
                ->andWhere($this->column . ' = ?', $this->baseObject->{$this->column})
                ->orderBy('ft.' . self::LEFT_FIELDNAME . ' DESC')
                ->limit(1);

        return $q->fetch();
    }

    public function getRoot()
    {
        $q = ORM::select($this->name . ' ft')
                ->where('ft.' . self::LEFT_FIELDNAME . ' = ?', 1)
                ->andWhere($this->column . ' = ?', $this->baseObject->{$this->column})
                ->limit(1);

        return $q->fetch();
    }

    public function getById($id)
    {
        $q = ORM::select($this->name . ' ft')
                ->where('ft.id = ?', (int)$id)
                ->andWhere($this->column . ' = ?', $this->baseObject->{$this->column})
                ->limit(1);

        return $q->fetch();
    }

    public function getPath()
    {
        $q = $this->getPathQuery();
        return $q->fetchAll();
    }

    public function getLevel()
    {
        $q = $this->getPathQuery();

        return $q->exec();
    }

    protected function getPathQuery()
    {
        $left = $this->baseObject->{self::LEFT_FIELDNAME};
        $right = $this->baseObject->{self::RIGHT_FIELDNAME};
        $q = ORM::select($this->name . ' ft')
                ->where('ft.' . self::LEFT_FIELDNAME . ' < ?', $left)
                ->andWhere('ft.' . self::RIGHT_FIELDNAME . ' > ?', $right)
                //->andWhere('ft.' . self::LEFT_FIELDNAME . ' > 1') // not root
                ->andWhere($this->column . ' = ?', $this->baseObject->{$this->refColumn})
                ->orderBy('ft.' . self::LEFT_FIELDNAME . ' ASC');

        return $q;
    }

    public function getTree($left = null, $right = null)
    {
        $model = get_class($this->baseObject);

        $left = ($left == null) ? $this->baseObject->{self::LEFT_FIELDNAME} : $left;
        $right = ($right == null) ? $this->baseObject->{self::RIGHT_FIELDNAME} : $right;

        $q = ORM::select($model . ' ft')
                ->where('ft.' . self::LEFT_FIELDNAME . ' BETWEEN ? AND ?', array($left + 1, $right - 1))
                ->andWhere($this->column . ' = ?', $this->baseObject->{$this->refColumn})
                ->orderBy('ft.' . self::LEFT_FIELDNAME . ' ASC');

        return self::makeTree($q->fetchAll());
    }

    protected static function addChildrenToModel($model, $children)
    {
        $arr = $model->Childrens;
        $arr []= $children;
        $model->Childrens = $arr;
    }

    public static function makeTree($collection)
    {
        $nodes = array();
        $right = array();
        $parents = array();
        foreach ($collection as $el) {
            $rightEl = $el->{self::RIGHT_FIELDNAME};
            $el->Childrens = array();

            $count = count($right);
            if ($count > 0) {
                // check if we should remove a node from the stack
                while ($right[($count-1)] < $rightEl) {
                    array_pop($right);
                    array_pop($parents);
                    $count = count($right);
                    if ($count == 0) {
                        break;
                    }
                }
            }
            // add children
            if (count($parents) > 0) {
                $parent = end($parents);
                self::addChildrenToModel($parent, $el);
            } else {
                $nodes []= $el;
            }

            // add this node to the stack  
            $right [] = $rightEl;
            $parents []= $el;
        }
        return $nodes;
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
