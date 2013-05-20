<?php

class ComNewsChannel_Controller_Admin extends CMS_Component_Controller
{
    public function defaultAction()
    {
        $form = new Html_Form('list');
        $table = new ComNewsChannel_Form_Table_List('table');

        $form->addElement(new ComNewsChannel_Form_Element_CategorySelect('categories'), 'categories')
             ->checkboxes(false);
        $user = CMS_User::getUser();
        $userId = $user->hasRight('ComNewsChannel', ComNewsChannel::ACL_CAN_MANAGE_NEWS) ? null : $user->id;

        $collection = ComNewsChannel_Model_Article::getCollection(false, null, null, null, $userId);

        $form->addElement($table)
             ->collection($collection)
             ->pager('ComNewsChannel.List');
        
        $user = CMS_User::getUser();
        $this->view->assign('canCreate', $user->hasRight('ComNewsChannel', ComNewsChannel::ACL_CAN_CREATE_NEWS));
        
        $this->view->assign('form', $form);
        $this->view->display('admin/main');
    }

    public function editAction($id = null)
    {
        $form = new ComNewsChannel_Form_Edit();

        $this->component->addWebservice('ComNewsChannel_Webservice_News');
        if (empty($id)) {
            $user = CMS_User::getUser();
            if ($user->hasRight('ComNewsChannel', ComNewsChannel::ACL_CAN_CREATE_NEWS)){
                $this->component->NewMenu->activate();

                $newsitem = ComNewsChannel_Model_Article::create();
                $newsitem->hits = rand(10, 20);
            } else {
              Url::redirect($this->component->urlFor('ComNewsChannel.List'));
            }
        } else {
            $this->component->EditMenu->activate();

            $newsitem = ComNewsChannel_Model_Article::getById(intval($id));
            $this->view->assign('newsitem', $newsitem);
            $this->view->assign('images', $newsitem->Images->get());
        }

        $form->dataBind($newsitem);
        if ($form->isPostBack() && $form->validate()){
            $form->save();
            Url::redirect($this->component->urlFor('ComNewsChannel.Edit', array('id' => $form->DataBindedObject->id)));
        }

        $this->view->assign('form', $form);

        if (class_exists('ComVK')) {
            $this->view->assign('vk_group', CMS_Option::get(ComVK::ACCESS_SECRET_OPTION, false) && CMS_Option::get(ComVK::ACCESS_TOKEN_OPTION, false));
        }

        $this->view->display('admin/edit');
    }

    public function commentsAction()
    {
        $form = new Html_Form('list');
        $table = new ComComments_Form_Table_Comments('table');

        $collection = ComNewsChannel_Model_Comment::getCommentsCollection();

        $table = $form->addElement($table);
        $table->collection($collection)
              ->pager('ComNewsChannel.Comments');

        $this->view->assign('form', $form->toString());
        $this->view->display('admin/comments');
    }

    public function settingsAction()
    {
        $form = new ComNewsChannel_Form_Settings();

        $this->view->assign('form', $form);
        $this->view->display('admin/settings');
    }
}