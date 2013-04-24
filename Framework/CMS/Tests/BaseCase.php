<?php

namespace Framework\CMS\tests;

using('Framework.System.ORM');

$connectionString = new \ORM_Adapter_Mysql(array('server' => '10.0.0.1', 'database' => 'orm_tests_db', 'username' => 'root', 'password' => ''));
\ORM_Connection_Manager::add($connectionString, 'default');

// @codeCoverageIgnoreStart
abstract class BaseCase extends \tests\BaseCase
{
}
// @codeCoverageIgnoreEnd