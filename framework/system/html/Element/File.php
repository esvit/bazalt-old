<?php

class Html_Element_File extends Html_Element_Input
{
    const DEFAULT_CSS_CLASS = 'bz-form-input-file';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('accept');     // Устанавливает фильтр на типы файлов, которые вы можете отправить через поле загрузки файлов.
        
        $this->validAttribute('readonly',  'boolean');  // Устанавливает, что поле не может изменяться пользователем.
        $this->validAttribute('maxlength', 'int');      // Максимальное количество символов разрешенных в тексте.
        $this->validAttribute('size',      'int');      // Ширина текстового поля.
        $this->validAttribute('value', 'mixed', false); // Значение элемента.

        $this->template('elements/file');

        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->type('file');
    }

    public function form(Html_Form $form = null)
    {
        if ($form != null) {
            $form->enctype(Html_Element_Form::MULTIPART_FORM_TYPE);
            return parent::form($form);
        }
        return $this->form;
    }

    public function value($value = null)
    {
        if ($value != null) {
            return parent::value($value);
        }
        $value = parent::value();
        if (isset($value['error']) && ($value['error'] == UPLOAD_ERR_NO_FILE)) {
            return null;
        }
        return $value;
    }

    public function validate()
    {
        $info = $this->value();
        if (!$info) {
            return true;
        }
        if ($info['error'] != UPLOAD_ERR_OK) {
            switch ($info['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $this->addError('error', 'The uploaded file exceeds the upload_max_filesize directive');
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $this->addError('error', 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
                break;
            case UPLOAD_ERR_PARTIAL:
                $this->addError('error', 'The uploaded file was only partially uploaded');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->addError('error', 'Missing a temporary folder');
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $this->addError('error', 'Failed to write file to disk');
                break;
            case UPLOAD_ERR_EXTENSION:
                $this->addError('error', 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop');
                break;
            }
            return false;
        }
        if (!is_uploaded_file($info['tmp_name'])) {
            $this->addError('file', 'Possible file upload attack');
            return false;
        }
        return true;
    }

    public function moveFile($destFile)
    {
        $info = $this->value();
        if (!isset($info['tmp_name'])) {
            return false;
        }   

        return move_uploaded_file($info['tmp_name'], $destFile);
    }
}