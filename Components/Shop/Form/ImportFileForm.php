<?php

class ComEcommerce_Form_ImportFileForm extends Html_Form
{
    protected $file;

    protected $fields;

    protected $data = array();

    protected $fieldsGroup;

    public function __construct($name = null, $attributes = array())
    {
        parent::__construct('importFile', $attributes);

        $this->addElement('validationsummary', 'errors');

        $this->file = $this->addElement('hidden', 'file');

        $this->fieldsGroup = $this->addElement('group');

        $group = $this->addElement('group', 'submitGroup');

        $group->addElement('button', 'post')
              ->content(__('Import', ComEcommerce::getName()))
              ->addClass('primary');
    }

    public function getData()
    {
        return $this->data;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFile($value)
    {
        $this->file->value($value);

        if (($handle = fopen(PUBLIC_DIR . $value, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if ($row == 0) {
                    $fieldTypes = ComEcommerce::getExportFields();
                    $n = 0;
                    foreach ($data as $field) {
                        if ($n++ == 0) {
                            continue;
                        }
                        $el = $this->fieldsGroup->addElement('select')
                                                            ->label($field);
                        foreach ($fieldTypes as $k => $ef) {
                            $el->addOption($ef, $k);
                        }
                        $this->fields []= $el;
                    }
                } else {
                    $this->data []= $data;
                }
                if (++$row > 5) {
                    break;
                }
            }
            fclose($handle);
        }
    }
}
