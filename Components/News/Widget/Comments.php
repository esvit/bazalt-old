<?php

class ComNewsChannel_Widget_Comments extends CMS_Widget_Component
{
    public function fetch($config)
    {
        // Comments
        $component = CMS_Bazalt::getComponent('ComNewsChannel');
        $item = $component->view->get('newsitem');

        if (!$item) {
            return;
        }
        $this->component->addWebservice('ComNewsChannel_Webservice_Comments');

        $this->component->addScript('js/models/comment.js');
        $this->component->addScript('js/collections/comments.js');
        $this->component->addScript('js/views/comment.js');
        $this->component->addScript('js/views/comments.js');
        $this->component->addScript('js/views/commentForm.js');

        $this->view->assign('item', $item);

        $user = CMS_User::getUser();
        $isAdmin = $component->hasRight(ComNewsChannel::ACL_CAN_MANAGE_COMMENTS);

        $comments = ComNewsChannel_Model_Comment::getCommentsForItem($item->id, !$isAdmin);
        $this->view->assign('comments', ORM_Relation_NestedSet::makeTree($comments));

        $json = array();
        foreach ($comments as $comment) {
            $json []= $comment->toArray();
        }
        $this->view->assign('jsonComments', json_encode($json));

        $this->view->assign('maxLevel', 5);

        $this->view->assign('is_administrator', $isAdmin);
        $this->view->assign('user', $user);

        if ($user->isGuest()) {
            $avatar = ComTracking::getAvatar(48);
        } else {
            $avatar = $user->getAvatar('[48x48]');
        }
        $this->view->assign('avatar', $avatar);

        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        $refferers = array ('Select component','News comments');
        
        $this->view->assign('refferers', $refferers);
        $this->view->assign('options', $this->options);
        return $this->view->fetch('widgets/comments-settings');
    }

}