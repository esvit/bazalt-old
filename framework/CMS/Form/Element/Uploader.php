<?php

class CMS_Form_Element_Uploader extends Html_FormElement implements IEventable
{
    public $eventOnUploadComplete = Event::EMPTY_EVENT;

    public function __construct($name, $attributes = array())
    {
        if(!isset($attributes['size'])) {
            $attributes['size'] = 'uploader_preview';
        }
        if(!isset($attributes['size_limit'])) {
            $attributes['size_limit'] = 16 * 1024 * 1024;// max file size in bytes
        }
        if(!isset($attributes['allowed_extensions'])) {
            $attributes['allowed_extensions'] = array();//'jpg', 'jpeg', 'png', 'gif');
        }

        parent::__construct($name, $attributes);
    }

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('name', 'string', false);
        $this->validAttribute('size', 'string', false);
        $this->validAttribute('size_limit', 'int', false);
        $this->validAttribute('allowed_extensions', 'array', false);

        Scripts::addModule('FileUploader');
        
        $this->template('elements/uploader');
        $this->javascriptTemplate('elements/javascript/uploader');
    }

    protected function isPostBack()
    {
        return (strToLower($_SERVER['REQUEST_METHOD']) == 'post') && isset($_REQUEST[$this->id()]);
    }
    
    public function toString()
    {
        if($this->isPostBack()) {
            $this->upload();
        }
        return parent::toString();
    }
    
    public function buildParams()
    {
        $params = array();
        $params[$this->id()] = true;
        return json_encode($params);
    }

    public function getAllowedExtensions()
    {
        if (count($this->attributes['allowed_extensions']) == 0) {
            return;
        }
        return '"' . implode('", "', $this->attributes['allowed_extensions']) . '"';
    }

    protected function upload()
    {
        using('Framework.System.Uploader');

        $uploader = new Uploader_Base($this->attributes['allowed_extensions'], $this->attributes['size_limit']);
        $result = $uploader->handleUpload(UPLOAD_DIR, PUBLIC_DIR);
        $this->OnUploadComplete($result);
        if ($result['success']) {
            $result['url'] = $result['filename'];
        }
        print json_encode($result);
        exit;
    }
}
