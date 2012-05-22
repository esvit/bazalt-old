<?php
/**
 * ORM
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
 * @category    System
 * @package     ORM
  * @subpackage Generator
 * @author      Vitalii Savchuk <esvit666@gmail.com>
 * @author      Alex Slubsky <aslubsky@gmail.com>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version     $Revision: 104 $
 * @link        http://bazalt.org.ua/
 */

using('Framework.System.ORM');

/*
using('Framework.System.ORM.Generator');
$g = new DBGenerator();
$g->generateFromModel(ConnectionManager::getConnection(), null, TEMP_DIR.'/out.sql', null);
exit;
*/

/**
 * ORM
 *
 * @category   System
 * @package    ORM
 * @subpackage Generator
 * @author     Vitalii Savchuk <esvit666@gmail.com>
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    $Revision: 104 $
 * @link       http://bazalt.org.ua/
 */
class DBGenerator extends Object// implements IConsoleCommand, IEventable
{
    public $eventOnBeforeGenerate = Event::EMPTY_EVENT;
    
    public $eventOnGenerateComplete = Event::EMPTY_EVENT;
    
    public $eventOnGenerateTableStructure = Event::EMPTY_EVENT;
    
    public $eventOnGenerateTableRelations = Event::EMPTY_EVENT;
    
    public $eventOnGenerateTableData = Event::EMPTY_EVENT;
    
    protected $connection;
    
    protected $shema;
    
    protected $out = null;
    
    protected $data = array();
    
    protected $modelClasses = array();

    public function __construct()
    {}

    public function generateFromModel(DataBaseConnection $connString = null, $path, $out)
    {
        // $this->OnBeforeGenerate();
        
        $this->connection = $connString;
        $this->shema = $connString->ConnectionString->Database;
        $this->out = $out;

        DataModelManager::add(new DataModel($path));
        //CMS_Bazalt::initAllComponents();
        $this->modelClasses = ORM::getModelClasses();

        // if(!ORM::$modelClasses) {
            // ORM::detectModelClasses();
        // }
        
        // if( file_exists( $dataFile ) ) {
            // $xmlstr = file_get_contents( $dataFile  );
            // if( $xmlstr == false ) {
                // throw new Exception( 'Cannot read the file '.$dataFile  );
            // }
            // $xml = new SimpleXMLElement($xmlstr);
            
            // foreach(ORM::$modelClasses as $mod) {
                // if( !isset( $xml->$mod ) ) {
                    // continue;
                // }
                // if( !array_key_exists( $mod, $this->data ) ) {
                    // $this->data[$mod] = array();
                // }
                // foreach( (array)$xml->$mod as $data ) {
                    // $data = (array)$data;
                    // $fields = array();
                    // $values = array();
                    // foreach( $data as $field=>$value ) {
                        // $fields[] = '`'.$field.'`';
                        // $values[] = '\''. addslashes( trim( $value ) ).'\'';
                    // }
                    
                    // $sql = 'INSERT INTO `'.ORMRecord::getTableName($mod).'` ( '.implode(',',$fields).' ) VALUES ('.implode(',',$values) .');';                    
                    // $this->data[$mod][] = $sql;
                // }
            // }
        // }      
        
        if( !is_null( $this->out ) ) {
            $handle = fopen($this->out, 'w');            
            fclose($handle);
        }
        
        //$contetnt = 'SET FOREIGN_KEY_CHECKS = 0;'."\n\n";
        $this->generateTablesStructure();
        // $this->generateTablesRelations();
        // $this->generateTablesData();
        //$contetnt = trim( $contetnt );
        //print $contetnt;

    }
    
    // protected function dropOldRelations()
    // {
        // $q = new ORMQuery('SELECT 
            // TABLE_NAME as `table`,
            // CONSTRAINT_NAME as `constraint`
        // FROM
            // information_schema.KEY_COLUMN_USAGE
        // WHERE
            // CONSTRAINT_SCHEMA = \'' . $this->shema . '\'
            // AND REFERENCED_TABLE_SCHEMA IS NOT NULL');
        
        // $rels = $q->fetchAll();
        // $content = array();
        // foreach( $rels as $rel ) {
            // $content[] = 'ALTER TABLE `'.$rel->table.'` DROP FOREIGN KEY `'.$rel->constraint.'`;';
        // }
        
        // return implode("\n",$content)."\n";
    // }
    
    protected function generateTablesStructure()
    {
        //$content = 'SET FOREIGN_KEY_CHECKS = 0;'."\n";
        $content = array();
        $content[] = '-- ';
        $content[] = '-- Drop old keys';
        $content[] = '-- ';        
        // $content[] = $this->dropOldRelations();
        // $this->OnGenerateTableStructure();
        foreach($this->modelClasses as $model) {
            $obj = new $model();
            $content[] = $this->generateTableStructure($obj);
        }
        //$content .= 'SET FOREIGN_KEY_CHECKS = 1;'."\n";
        $this->runSql( implode("\n",$content) );
    }
    
    // protected function generateTablesRelations()
    // {
        // $this->OnGenerateTableRelations();
        // $relations = array();
        
        // if(!ORM::$modelClasses) {
            // ORM::detectModelClasses();
        // }
        
        // foreach(ORM::$modelClasses as $mod) {
            // $modObj = new $mod();            
            // if( is_array( $modObj->References ) ) {
                // foreach( $modObj->References as $reference ) {
                    // $relations = array_merge($relations, $reference->generateSql( $mod ));
                // }
            // }
        // }
        // /*print_r($relations);exit;        */

        // $content = array();
        // $content[] = '-- ';
        // $content[] = '-- Tables relations';
        // $content[] = '-- ';
        
        // foreach($relations as $relation) {
            // $content[] = $relation;
        // }

        // $this->runSql( implode("\n",$content) );
    // }
    
    // protected function generateTablesData()
    // {
        // $contetnt = '';
        // $this->OnGenerateTableData();
        // foreach(ORM::$modelClasses as $mod) {
            // $obj = new $mod();
            // $contetnt .= $this->generateTableData( $obj )."\n";
        // }
        // $this->runSql( $contetnt );
    // }    
    
    protected function runSql( $contetnt )
    {
        $contetnt = "\n".trim( $contetnt );
        if( is_null( $this->out ) ) {
            $q = new ORMQuery( $contetnt );
            $q->fetch();
        } else {
            $handle = fopen($this->out, 'a');
            fwrite($handle, $contetnt);
            fclose($handle);
            //file_put_contents( $this->out, $contetnt);
        }
    }

    protected function generateTableStructure($model)
    {
        $tableName = BaseORMRecord::getTableName(get_class($model));
        $content = array();
        $content[] = '-- ';
        $content[] = '-- Structure of table `'.$tableName.'`';
        $content[] = '-- ';
        $content[] = 'DROP TABLE IF EXISTS `'.$tableName.'`;';
        $content[] = 'CREATE TABLE IF NOT EXISTS `'.$tableName.'` (';

        $fields = array();
        foreach($model->Columns as $column) {
            $fields[] = $this->getFieldContent($column);// ."\n";
        }
        $fields[] = $this->getPrimaryKeys($model);
        $fields = array_merge($fields, $this->getIndexes($model));
        
        $content[] = implode(','."\n",$fields);
        $content[] = ') ENGINE='.($model->Engine ? $model->Engine : 'InnoDB').' DEFAULT CHARSET=utf8;';
        
        return implode("\n",$content);
    }
    
    // protected function generateTableRelations($model)
    // {
        // $content = array();
        // $content[] = '-- ';
        // $content[] = '-- Relations of table `'.ORMRecord::getTableName($model).'`';
        // $content[] = '-- ';        
        
        // $refs = trim( $this->getReferences($model) );
        // if( empty( $refs ) ) {           
            // return null;
        // } else {
            // $content[] = $refs;
        // }
        
        // return implode("\n",$content);
    // }
    
    // protected function generateTableData($model)
    // {
        // $modelName = get_class($model);
        
        // $content = array();
        // $content[] = '-- ';
        // $content[] = '-- Data dump of table `'.ORMRecord::getTableName($model).'`';
        // $content[] = '-- ';
        
        // if( array_key_exists( $modelName, $this->data ) ) {
            // $content[] = 'LOCK TABLE `'.ORMRecord::getTableName($model).'` WRITE;';
            // $content = array_merge( $content, $this->data[$modelName] );
            // $content[] = 'UNLOCK TABLES;';
        // } else {
            // return null;
        // }
        
        // return implode("\n",$content);
    // }
    
    protected function getFieldContent($column)
    {
        $content = array();
        $content[] = '`'.$column->Name.'`';
        $content[] = $column->Options['type'];
        $content[] = ($column->isUnsigned() ? ' UNSIGNED' : '');
        $content[] = ($column->isNullable() ? ' NULL' : ' NOT NULL');
        $content[] = ($column->isAutoIncrement() ? ' AUTO_INCREMENT' : '');
        $content[] = ($column->hasDefault() ? ' DEFAULT '.$column->getDefault() : '');
        return '    '.trim(implode(' ',$content));
    }

    protected function getPrimaryKeys($model)
    {
        $keys = array();
        $pKeys = BaseORMRecord::getPrimaryKeys(get_class($model));
        foreach($pKeys as $pKey) {
            $keys[] = '`'.$pKey->Name.'`';
        }
        if( count($keys) > 0 ) {  
            return 'PRIMARY KEY  ('.implode(',', $keys).')';
        } else {
            return  null;
        }
    }
    
    protected function getIndexes($model)
    {
        $res = array();
        $indexes = $model->getIndexes();
        if (is_array($indexes)) {
            foreach($indexes as $index) {
                $res []= $index->toSql();
                // print_r($index);
            }
        }
        return $res;
    }
    
    // protected function getReferences($model)
    // {    
        // $refs = array();
        // $modelName = get_class($model);
       
        // if( array_key_exists( $modelName, $this->relations ) ) {
            // foreach( $this->relations[$modelName] as $relationName=>$relation ) {
                // $ref = $relation->generateSql( $modelName );
                // if( $ref ) {
                    // $refs[] = $ref;
                // }
            // }
        // }

        // return implode("\n",$refs);
    // }
}