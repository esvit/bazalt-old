<?php

class CMS_Controller_User extends CMS_Controller_Abstract
{
    protected $view = null;

    public function preAction($action, $args)
    {
        $this->view = CMS_Application::current()->View;
    }

    public function loginAction()
    {
        CMS_Response::noCache();
        
        if(CMS_User::isLogined()) {
            Datatype_Url::redirect(CMS_Mapper::urlFor('CMS.Profile'));
        }

        $form = new CMS_Form_Login();
        $form->backUrl(Session::Singleton()->backUrl);

        if ($form->isPostBack()) {
            $form->value($_POST);
            if ($form->validate()) {
                $backUrl = $form->backUrl();
                if (empty($backUrl)) {
                    Datatype_Url::redirect(CMS_Mapper::urlFor('CMS.Profile'));
                } else {
                    Datatype_Url::redirect($backUrl);
                }
            }
        }

        $this->view->assign('form', $form);
        $this->view->display('page.login');
    }

    public function logoutAction()
    {
        CMS_Response::noCache();
        CMS_User::logout();

        //$url = new Url('/');
        //$url->setParams(array('rnd' => time()));

        Url::redirect(CMS_Mapper::urlFor('home'));
    }

    public function profileAction()
    {
        $permitions = CMS_Component::getAllRoles();

        $this->view->assign('permitions', $permitions);
        $this->view->assign('user', CMS_User::getUser());
        $this->view->display('page.profile');
    }

    public function setRoleAction($roleId)
    {
        CMS_User::setCurrentRole((int)$roleId);
        DataType_Url::redirect(CMS_Mapper::urlFor('home'));
    }

    public function captchaAction($element)
    {
        using('Framework.Vendors.KCaptcha');
        $captcha = new KCaptcha_Base();
        Session::Singleton()->{$element} = $captcha->getKeyString();
        exit;
    }
    
    //deprecated. TODO - remove it
    public function uploadAction()
    {
        using('Framework.System.Uploader');
        $allowedExtensions = array();
        // max file size in bytes
        $sizeLimit = 16 * 1024 * 1024;

        $uploader = new Uploader_Base($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload(UPLOAD_DIR);

        if ($result['success']) {
            /*if(ComGallery::isImage($result['filename'])) {
                $img = WideImage::load(UPLOAD_DIR . $result['filename'])->asTrueColor();
                if($img->getWidth() > ComGalleryPhoto::MAX_WIDTH || $img->getHeight() > ComGalleryPhoto::MAX_HEIGHT) {
                    $img = $img->resize(ComGalleryPhoto::MAX_WIDTH, ComGalleryPhoto::MAX_HEIGHT);
                    $img->saveToFile(UPLOAD_DIR . $result['filename']);
                }
            }*/
            $size = isset($_REQUEST['size']) ? $_REQUEST['size'] : 'big';
            $result['url'] = relativePath(UPLOAD_DIR . $result['filename']);
            $result['thumb'] = CMS_Image::getThumb($result['url'], $size);
        }
        print json_encode($result);
        exit;
    }
}