<?php

abstract class CMS_Model_Base_Record extends ORM_Record
{
    protected static $disabledSearchFields = array();

    public static function disableSearchField($model, $name)
    {
        self::$disabledSearchFields[$model][$name] = $name;
    }

    public function toArray()
    {
        $res = array();

        foreach ($this->getColumns() as $column) {
            $fieldName = $column->name();
            $res[$fieldName] = $this->$fieldName;
        }
        return $res;
    }

    public function toSearchIndex()
    {
        $res = array();

        foreach ($this->getColumns() as $column) {
            $fieldName = $column->name();
            $disabledFields = array();;
            if (array_key_exists(get_class($this), self::$disabledSearchFields)) {
                $disabledFields = self::$disabledSearchFields[get_class($this)];
            }
            if (in_array($fieldName, $disabledFields) || $fieldName == 'id') {
                continue;
            }
            switch ($column->dataType()) {
            case 'timestamp':
            case 'datetime':
                $res[$fieldName] = strToTime($this->{$fieldName});
                break;
            default:
                $res[$fieldName] = $this->{$fieldName};
                break;
            }
        }
        return $res;
    }

    public static function getModelSearchFields($model)
    {
        $tableName = ORM_BaseRecord::getTableName($model);
        $columns = ORM_BaseRecord::getAllColumns($tableName);

        $result = array(
            'fields' => array(),
            'attributes' => array()
        );
        foreach ($columns as $column) {
            if (in_array($column->name(), self::$disabledSearchFields[$model]) || $column->name() == 'id') {
                continue;
            }
            switch ($column->dataType()) {
            case 'float':
            case 'int':
            case 'tinyint':
            case 'timestamp':
            case 'datetime':
                $type = $column->dataType();
                if ($type == 'tinyint') {
                    $type = 'int';
                }
                if ($type == 'datetime') {
                    $type = 'timestamp';
                }
                $attr = array(
                    'name' => $column->name(),
                    'type' => $type
                );
                $options = $column->options();
                if (isset($options['defult'])) {
                    $attr['default'] = $options['defult'];
                }
                if ($column->length()) {
                    $attr['bits'] = $column->length();
                }
                $result['attributes'][] = $attr;
                break;
            default:
                $result['fields'][] = $column->name();
                break;
            }
        }
        return $result;
    }
}
