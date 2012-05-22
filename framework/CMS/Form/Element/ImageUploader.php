<?php

class CMS_Form_Element_ImageUploader extends CMS_Form_Element_Uploader
{
    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('text', 'string', false);

        $this->allowed_extensions(array('jpg', 'jpeg', 'png', 'gif'));
        $this->text(__('Drop files here to upload', 'CMS'));

        Scripts::addModule('Fancybox');

        $this->template('elements/imageuploader');
        $this->javascriptTemplate('elements/javascript/imageuploader');
    }

    public function ajaxGetImages()
    {
    
    }

    public function OnUploadComplete($result)
    {
        if ($result['success']) {
            $result['thumb'] = CMS_Image::getThumb($result['filename'], $this->size());
            $result['url'] = $result['filename'];
        }
        print json_encode($result);
        exit;
    }
}
