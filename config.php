<?php

using('Framework.System.ORM');
$connectionString = new ORM_Adapter_Mysql([
    'server' => 'localhost',
    'port' => '3306',
    'database' => 'bazalt_cms',
    'username' => 'root',
    'password' => 'gjhndtqy777'
]);
ORM_Connection_Manager::add($connectionString, 'default');