<?php

class CMS_Form_Element_Wysiwyg_Imperavi extends Html_jQuery_Textarea
{
    public function toString()
    {
        $this->style('width: 100%; height: 320px;');

        Scripts::addLibrary('Imperavi');

        $js = '' . $this->id() . ' = $("#' . $this->id() . '").redactor({ toolbas: "mini" });';

        Html_Form::addOnReady($js);
        Html_Form::addOnReady('$(".imp_redactor_box").width("auto");');
        return parent::toString();
    }
}