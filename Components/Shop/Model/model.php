<?php

if (!isset($argv[1])) {
    exit('No file');
}
$fileName = $argv[1];
$file = file_get_contents($fileName);

$pattern = '/class Com(\w+)_Model_(\w+) extends (\w+)_Model_Base_(\w+)/i';
$replacement = "namespace Components\\Shop\\Model;\n\nclass $2 extends Base\\\\$4";

$file = preg_replace($pattern, $replacement, $file);

$pattern = "/const MODEL_NAME = '(\w+)_(\w+)';/i";
$replacement = "const MODEL_NAME = 'Components\\Shop\\Model\\\\$2';";

$file = preg_replace($pattern, $replacement, $file);

$pattern = '/new(.*)ORM_Relation_/i';
$file = preg_replace($pattern, 'new \Bazalt\ORM\Relation\', $file);

$pattern = "/Com(.*)_Model_(.*)/i";
$replacement = "Components\\\\$1\\Model\\\\$2";
$file = preg_replace($pattern, $replacement, $file);

$pattern = "/CMS_Model_(.*)/i";
$replacement = "\Framework\CMS\Model\\\\$1";
$file = preg_replace($pattern, $replacement, $file);

echo $file;

file_put_contents($fileName, $file);