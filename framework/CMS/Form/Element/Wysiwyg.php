<?php

class CMS_Form_Element_Wysiwyg extends Html_FormElement
{
    const DEFAULT_CSS_CLASS = 'bz-form-wysiwyg';

    protected $editor = null;

    public function __construct($name, $attributes = array())
    {
        parent::__construct($name, $attributes);

        $this->createWysiwygEditor(parent::id());

        $this->template('elements/wysiwyg');
    }

    protected function createWysiwygEditor($name)
    {
        $this->editor = new Html_jQuery_TinyMCE($name);

        $this->editor->addFilter(new Html_Filter_HTMLPurifier(), array(
                                'Core.Encoding' => 'UTF-8',
                                'Filter.Custom' => array(new HTMLPurifier_Filter_Break()),
                                'HTML.Doctype' => 'HTML 4.01 Transitional',
                                'AutoFormat.RemoveEmptyTagsWithNBSPRemove' => 'font',
                                'AutoFormat.RemoveEmptyTagsWithNBSP' => true,
                                'AutoFormat.RemoveEmptyTagsWithNBSPAdd' => 'p'
                            )
                        );
    }

    public function __clone()
    {
        $this->createWysiwygEditor($this->originalName);
    }

    public function id($id = false)
    {
        return $this->editor->id($id);
    }

    public function form(Html_Form $form = null)
    {
        if ($this->editor) {
            return $this->editor->form($form);
        }
        return parent::form($form);
    }

    public function container(Html_ContainerElement $container = null)
    {
        if ($this->editor) {
            return $this->editor->container($container);
        }
        return parent::container($container);
    }

    public function name($name = null)
    {
        if (!$this->editor) {
            return parent::name($name);
        }
        return $this->editor->name($name);
    }

    public function value($value = null)
    {
        return $this->editor->value($value);
    }

    public function defaultValue($value = null)
    {
        return $this->editor->defaultValue($value);
    }

    /*public function toString()
    {
        $view = $this->view();
        $view->assign('element', $this);
        $before = $this->beforeTemplate();
        $after = $this->afterTemplate();
        $str = '';
        if (!empty($before)) {
            $str .= $view->fetch($before);
        }

        if (!$this->form) {
        print_r($this);exit;
            throw new Exception('Form not found');
        }
        $this->form->registerJavascript($this);

        $str .= $view->fetch($this->template());
        if (!empty($after)) {
            $str .= $view->fetch($after);
        }
        return $str;
    }*/
}