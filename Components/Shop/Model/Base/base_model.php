<?php

if (!isset($argv[1])) {
    exit('No file');
}
$fileName = $argv[1];
$file = file_get_contents($fileName);

$pattern = '/abstract class (\w+)_(\w+) extends (\w+)/i';
$replacement = "namespace Components\\Pages\\Model\\Base;\n\nabstract class $2 extends \\Framework\\CMS\\ORM\\Record";

$file = preg_replace($pattern, $replacement, $file);

$pattern = "/const MODEL_NAME = '(\w+)_(\w+)';/i";
$replacement = "const MODEL_NAME = 'Components\\Pages\\Model\\\\$2';";

$file = preg_replace($pattern, $replacement, $file);

$pattern = '/\n(.*)public( static)? function getById\(\$id\)(.*)\n(.*){(.*)\n(.*)return parent::getRecordById\(\$id, self::MODEL_NAME\);(.*)\n(.*)}(.*)\n/mi';
$file = preg_replace($pattern, '', $file);

$pattern = '/\n(.*)public( static)? function getAll\(\$limit = null\)(.*)\n(.*){(.*)\n(.*)return parent::getAllRecords\(\$limit, self::MODEL_NAME\);(.*)\n(.*)}(.*)\n/mi';
$file = preg_replace($pattern, '', $file);

$pattern = '/\n(.*)public( static)? function select\(\$fields = null\)(.*)\n(.*){(.*)\n(.*)return ORM::select\(self::MODEL_NAME, \$fields\);(.*)\n(.*)}(.*)\n/mi';
$file = preg_replace($pattern, '', $file);

$pattern = '/\n(.*)public( static)? function insert\(\$fields = null\)(.*)\n(.*){(.*)\n(.*)return ORM::insert\(self::MODEL_NAME, \$fields\);(.*)\n(.*)}(.*)\n/mi';
$file = preg_replace($pattern, '', $file);

$pattern = '/\n(.*)public function initRelations()(.*)\n(.*){(.*)\n(.*)}(.*)\n(.*)/mi';
$file = preg_replace($pattern, '', $file);

$pattern = '/\n(.*)public function initPlugins()(.*)\n(.*){(.*)\n(.*)}(.*)\n(.*)/mi';
$file = preg_replace($pattern, '', $file);

$pattern = '/new(.*)ORM_Relation_/i';
$file = preg_replace($pattern, 'new \Bazalt\ORM\Relation\', $file);

$pattern = '/ORM_Plugin_/i';
$file = preg_replace($pattern, 'Bazalt\ORM\Plugin\\', $file);

$pattern = '/\$this->hasPlugin\(\'CMS_ORM_Localizable\', array\((.*)\n(.*)(.*)\'fields\' => array\((.*)\),(.*)\n(.*)\'type\' =>(.*)\n(.*)\)\);/mi';
$file = preg_replace($pattern, '$this->hasPlugin(\'Framework\CMS\ORM\Localizable\', [$4]);', $file);

$pattern = "/Com(.*)_Model_(.*)/i";
$replacement = "Components\\\\$1\\Model\\\\$2";
$file = preg_replace($pattern, $replacement, $file);

$pattern = "/CMS_Model_(.*)/i";
$replacement = "\Framework\CMS\Model\\\\$1";
$file = preg_replace($pattern, $replacement, $file);

echo $file;

file_put_contents($fileName, $file);