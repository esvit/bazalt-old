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
class CMS_ORM_Relation_LocalizableNestedSet extends ORM_Relation_NestedSet
{
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

        $lang = CMS_ORM_Localizable::getLanguage();

        $left = $this->baseObject->{self::LEFT_FIELDNAME} + 1;
        $right = $this->baseObject->{self::RIGHT_FIELDNAME} - 1;
        $q = ORM::select($this->name . ' ft')
                ->innerJoin($this->name . 'Locale ftl', array('id', 'ft.id'))
                ->where('ft.' . self::LEFT_FIELDNAME . ' BETWEEN ? AND ?', array($left, $right))
                ->andWhere('ftl.lang_id = ?', $lang->id)
                ->andWhere('ft.'.$this->column . ' = ?', $this->baseObject->{$this->column})
                ->orderBy('ft.' . self::LEFT_FIELDNAME . ' ASC');

        if ($depth != null) {
            $q->andWhere(self::DEPTH_FIELDNAME . ' <= ?', $this->baseObject->depth + $depth);
        }
        $this->applyAddParams($q);
        return $q;
    }

    protected function getPathQuery()
    {
        $lang = CMS_ORM_Localizable::getLanguage();

        $left = $this->baseObject->{self::LEFT_FIELDNAME};
        $right = $this->baseObject->{self::RIGHT_FIELDNAME};
        $q = ORM::select($this->name . ' ft')
                ->innerJoin($this->name . 'Locale ftl', array('id', 'ft.id'))
                ->where('ft.' . self::LEFT_FIELDNAME . ' < ?', $left)
                ->andWhere('ft.' . self::RIGHT_FIELDNAME . ' > ?', $right)
                ->andWhere('ftl.lang_id = ?', $lang->id)
                //->andWhere('ft.' . self::LEFT_FIELDNAME . ' > 1') // not root
                ->andWhere($this->column . ' = ?', $this->baseObject->{$this->refColumn})
                ->orderBy('ft.' . self::LEFT_FIELDNAME . ' ASC');

        return $q;
    }
}
