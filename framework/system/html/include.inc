<?php

using('Framework.System.Session');
using('Framework.System.View');
using('Framework.System.Assets');

Core_Autoload::registerNamespace('Html', dirname(__FILE__));

Html_Form::registerElement('text',                  'Html_Element_Text');
Html_Form::registerElement('checkbox',              'Html_Element_Checkbox');
Html_Form::registerElement('radio',                 'Html_Element_Radio');
Html_Form::registerElement('password',              'Html_Element_Password');
Html_Form::registerElement('hidden',                'Html_Element_Hidden');
Html_Form::registerElement('fieldset',              'Html_Element_Fieldset');
Html_Form::registerElement('select',                'Html_Element_Select');
Html_Form::registerElement('optiongroup',           'Html_Element_OptionGroup');
Html_Form::registerElement('flasher',               'Html_Element_Flasher');
Html_Form::registerElement('validationsummary',     'Html_Element_ValidationSummary');
Html_Form::registerElement('group',                 'Html_Element_Group');
Html_Form::registerElement('inputbutton',           'Html_Element_InputButton');
Html_Form::registerElement('textarea',              'Html_Element_Textarea');
Html_Form::registerElement('file',                  'Html_Element_File');
Html_Form::registerElement('image',                 'Html_Element_Image');
Html_Form::registerElement('button',                'Html_Element_Button');
Html_Form::registerElement('number',                'Html_Element_Number');
Html_Form::registerElement('html',                  'Html_Element_Literal');

Html_Form::registerElement('dateselect',            'Html_Custom_DateSelect');
Html_Form::registerElement('timeselect',            'Html_Custom_TimeSelect');
Html_Form::registerElement('datetimeselect',        'Html_Custom_DateTimeSelect');

Html_Form::registerElement('tabs',                  'Html_jQuery_Tabs');