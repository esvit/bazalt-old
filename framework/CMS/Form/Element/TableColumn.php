<?php

class CMS_Form_Element_TableColumn extends Html_FormElement
{
    protected $table = null;

    protected $name = null;

    protected $title = null;

    protected $width = null;

    protected $columnName = null;

    protected $sorting = null;

    protected $canSorting = false;

    protected $headerTemplate = 'cms/table/header/default';

    protected $columnTemplate = 'cms/table/column/default';

    public function __construct($name)
    {
        parent::__construct($name);

        $this->id($name);
        $this->columnName = $name;

        $this->javascriptTemplate('cms/table/javascript/column');
    }

    public function table($table = null)
    {
        if ($table === null) {
            return $this->table;
        }
        $this->table = $table;
        return $this;
    }

    public function sorting($sorting = false)
    {
        if ($sorting === false) {
            return $this->sorting;
        }
        $this->sorting = $sorting;
        return $this;
    }

    public function canSorting($canSorting = null)
    {
        if ($canSorting === null) {
            return $this->canSorting;
        }
        $this->canSorting = $canSorting;
        return $this;
    }

    public function width($width = null)
    {
        if ($width === null) {
            return $this->width;
        }
        if (!is_numeric($width)) {
            throw new InvalidArgumentException('Width must be an integer');
        }
        $this->width = (int)$width;
        return $this;
    }

    public function headerTemplate($template = null)
    {
        if ($template === null) {
            return $this->headerTemplate;
        }
        $this->headerTemplate = $template;
        return $this;
    }

    public function columnTemplate($template = null)
    {
        if ($template === null) {
            return $this->columnTemplate;
        }
        $this->columnTemplate = $template;
        return $this;
    }

    public function columnName($name = null)
    {
        if ($name === null) {
            return $this->columnName;
        }
        $this->columnName = $name;
        return $this;
    }

    public function title($title = null)
    {
        if ($title === null) {
            return $this->title;
        }
        $this->title = $title;
        return $this;
    }

    public function headerToString()
    {
        //$this->form->registerJavascript($this);

        $this->table->View->assign('element', $this);
        return $this->table->View->fetch($this->headerTemplate());
    }

    public function dataToString($data)
    {
        $this->table->View->assign('item', $data);
        // if (isset($data->{$this->name})) {
            $this->table->View->assign('data', $data->{$this->name});
        // } else {
            // $this->table->View->assign('data', null);
        // }
        $this->table->View->assign('element', $this);
        return $this->table->View->fetch($this->columnTemplate());
    }
}