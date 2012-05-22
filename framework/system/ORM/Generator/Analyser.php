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
 * @category   System
 * @package    ORM
 * @subpackage Generator
 * @author     Vitalii Savchuk <esvit666@gmail.com>
 * @author     Alex Slubsky <aslubsky@gmail.com>
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    $Revision: 104 $
 * @link       http://bazalt.org.ua/
 */

using('Framework.System.ORM');

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
class Analyser extends Object implements IConsoleCommand, IEventable
{
    public $eventOnBeforeAnalyse = Event::EMPTY_EVENT;
    
    public $eventOnAnalyseComplete = Event::EMPTY_EVENT;
    
    protected $connection;
    
    protected $shema;
    
    public function analyse(DataBaseConnection $connString = null, $path)
    {
        $this->OnBeforeAnalyse();
        
        $this->connection = $connString;
        $this->shema = $connString->ConnectionString->Database;
        
        DataModelManager::add(new DataModel('default', $path));
        
        $msg = null;
        try {
            $this->compare();
            $this->OnAnalyseComplete(Console::OK_STATUS);
        } catch(Exception $e) {
            $msg = $e->getMessage();
            $this->OnAnalyseComplete(Console::FAILED_STATUS);
        }
        print $msg;
    }
    
    public function compare()
    {
        if(!ORM::$modelClasses) {
            ORM::detectModelClasses();
        }        
        
        $q = new ORMQuery('SHOW TABLES;');
        $tables = $q->fetchAll();
        $tabInfo = array();
        foreach($tables as &$table) {            
            $tabInfo[current((array)$table)] = array();
        }
        
        if( count($tables) != count(ORM::$modelClasses) ) {
            throw new Exception('You have '.count($tables).' tables / '.count(ORM::$modelClasses).' models');
        }
        
        foreach(ORM::$modelClasses as $mod) {
            $modObj = new $mod();
            $tableName = ORMRecord::getTableName($modObj);
            if( !array_key_exists($tableName,$tabInfo) ) {
                throw new Exception('Model table '.$tableName.' not found in database tables');
            }
            
            $q = new ORMQuery('SHOW FULL COLUMNS FROM `' . $tableName . '`;');            
            $columns = $q->fetchAll();
            foreach($columns as $column) {
                $tabInfo[$tableName][] = $column->Field;
            }
            
            foreach( $modObj->Columns as $column ) {
                if( !in_array($column->Name,$tabInfo[$tableName]) ) {
                    throw new Exception('Model column '.$column->Name.' not found in columns of table '.$tableName);
                }
            }
        }
        
        return true;
    }    
    
    public function runCommand($args)
    {
        $host = 'localhost';
        if( array_key_exists('host',$args['commands']) ) {
            $host = $args['commands']['host'];
        }
        $user = 'root';
        if( array_key_exists('user',$args['commands']) ) {
            $user = $args['commands']['user'];
        }
        $password = '';
        if( array_key_exists('password',$args['commands']) ) {
            $password = 'Pwd='.$args['commands']['password'].';';
        }
        $db = 'bazalt';
        if( array_key_exists('db',$args['commands']) ) {
            $db = $args['commands']['db'];
        }
        $path = SITE_DIR. '/cms/models';
        if( array_key_exists('path',$args['commands']) ) {
            $path = $args['commands']['path'];
        }
   
        ConnectionManager::add(new MysqlConnectionString('Provider=mysql;Server='.$host.';Database='.$db.';Uid='.$user.';'.$password));
        $this->analyse( ConnectionManager::getConnection(), $path );
    }    

    public function getHelp()
    {
        return <<<HELP

bazalt {{**}}generatedb{{}} {{*YELLOW*}}[-help]{{}} {{*GREEN*}}[-h localhost]{{}} {{*RED*}}[-p password]{{}} {{*BLUE*}}[-d database]{{}}
      {{*MAGENTA*}}[-p path]{{}}

{{*YELLOW*}}[-help]{{}} - Show this screen
{{*GREEN*}}[--host localhost]{{}} - Set host, as default {{**}}localhost{{}}
{{*RED*}}[--user user]{{}} - Set user
{{*RED*}}[--password password]{{}} - Set password
{{*BLUE*}}[--db database]{{}} - Set database
{{*MAGENTA*}}[--path path]{{}} - Set path
HELP;
    }
}