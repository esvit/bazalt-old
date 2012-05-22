<?php
/**
 * ORM.php
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * ORM
 *
 * @category   ORM
 * @package    BAZALT
 * @subpackage System
 * @copyright  2010 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */ 
class ORM_Relation_One2Many extends ORM_Relation_Abstract implements ORM_Interface_RelationMany
{
    /**
     * Евент OnAdd
     *
     * @var Event
     */
    public $eventOnAdd = Event::EMPTY_EVENT;
    
    /**
     * Евент OnRemove
     *
     * @var Event
     */
    public $eventOnRemove = Event::EMPTY_EVENT;
    
    /**
     * Constructor
     *
     * @param string $name             Назва моделі до якої іде звязок
     * @param string $column           Назва поля (стовпця) моделі від якої йде звязок
     * @param string $refColumn        Назва поля (стовпця) моделі до якої йде звязок     
     * @param string $additionalParams Масив додаткових параметрів, 
     *                                 які будуть враховуватись при вибірках по звязку
     */
    public function __construct($name, $column, $refColumn, $additionalParams = null)
    {
        $this->name = $name;
        $this->column = $column;
        $this->refColumn = $refColumn;
        $this->additionalParams = $additionalParams;
    }
    
    /**
     * Викликається при зверненні до об'єкту зв'язку
     * і повертає масив обєктів звязаної моделі, які відносяться до поточного обєкта
     *
     * @return array
     */
    public function get()
    {
        $q = $this->getQuery();
        if (!$q) {
            return null;
        }
        return $q->fetchAll($this->name);
    }

    /**
     * Генерує запит для вибірки звязаних обєктів
     *
     * @return SelectQueryBuilder
     */
    public function getQuery()
    {
        $c = $this->column;
        if (!isset($this->baseObject->$c)) {
            //throw new Exception(sprintf('Field %s of model %s is not set', $c, get_class($this->baseObject)));
            return null;
        }

        $idVal = $this->baseObject->$c;        
        $q = ORM::select($this->name . ' ft')
            ->andWhere('ft.' . $this->refColumn . ' = ?', $idVal);
        $this->applyAddParams($q);
        return $q;
    }

    /**
     * Генерує Sql скрипт для звязку @deprecated
     *
     * @param ORM_Record $model Модель до якої йде звязок
     * 
     * @return string
     */
    public function generateSql( $model )
    {
        $name = array($model,$this->name);
        sort($name);
        $ref = array();
        $ref[] = 'ADD KEY `'.ORM_Record::getTableName($this->name).'_'.DataType_String::fromCamelCase($this->refColumn).
                 '` (`'.DataType_String::fromCamelCase($this->refColumn).'`)';
        $ref[] = 'ADD CONSTRAINT `'.ORM_Record::getTableName($this->name).'_'.DataType_String::fromCamelCase($this->refColumn).
                 '` FOREIGN KEY (`'.DataType_String::fromCamelCase($this->refColumn).'`) REFERENCES `'.
                 DataType_String::fromCamelCase($model).'` (`'.DataType_String::fromCamelCase($this->column).'`) ON DELETE CASCADE';
        $content = 'ALTER TABLE `'.ORM_Record::getTableName($this->name).'` '."\n".implode(','."\n", $ref).';'; 
        return array( implode('_', $name) => $content ); 
    }

    /**
     * Створює зв'язок між поточним обєктом та обєктом $item
     *
     * @param ORM_Record $item Об'єкт, який потрібно додати
     *
     * @return void
     */
    public function add(ORM_Record $item)
    {
        $this->checkType($item);
        $this->OnAdd($this->baseObject, $item);
        
        $item->{$this->refColumn} = $this->baseObject->{$this->column};
        $item->save();
    }
    
    /**
     * Видаляє всі об'єкти по зв'язку
     *
     * @return void
     */   
    public function removeAll()
    {
        $q = ORM::delete($this->name)
                ->where($this->refColumn . ' = ?', $this->baseObject->id);

        $q->exec();
    }

    /**
     * Видаляє зв'язок між поточним обєктом та обєктом $item
     *
     * @param ORM_Record $item Об'єкт, який потрібно видалити
     *
     * @return void
     */
    public function remove(ORM_Record $item)
    {
        throw new DontDevelopedYetException();
    }

    /**
     * Перевіряє чи існує зв'язок між поточним обєктом та обєктом $item
     *
     * @param ORM_Record $item Об'єкт, який потрібно перевірити     
     *
     * @return bool
     */ 
    public function has(ORM_Record $item)
    {
        $this->checkType($item);
        
        return (bool)($item->{$this->refColumn} == $this->baseObject->{$this->column});
    }
}
