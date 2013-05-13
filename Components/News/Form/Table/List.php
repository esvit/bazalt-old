<?php

class ComNewsChannel_Form_Table_List extends CMS_Form_Element_Table
{
    protected $sortColumn = 0;
        
    public function getUrl($page)
    {
        return CMS_Mapper::urlFor('ComNewsChannel.List', array('?page' => $page));
    }

    public function ajaxDelete($ids)
    {
        return ComNewsChannel_Model_Article::deleteByIds($ids);
    }

    public function ajaxLoadByCategory($categoryId)
    {
        $root = ComNewsChannel_Model_Category::getSiteRootCategory();
        $category = ComNewsChannel_Model_Category::getById($categoryId);
        if (!$category || $category->category_id != $root->category_id) {
            throw new Exception('Category not found');
        }
        $this->view()->assign('selectedId', $categoryId);
        $this->collection(ComNewsChannel_Model_Article::getCollectionByCategory($category))
             ->pager('ComNewsChannel.List');

        return $this->toString();
    }

    public function ajaxGetPageCategory($categoryId, $page, $sortColumn, $sortDirection)
    {
        $category = ComNewsChannel_Model_Category::getById($categoryId);
        if (!$category) {
            throw new Exception('Category not found');
        }
        $this->collection(ComNewsChannel_Model_Article::getCollectionByCategory($category))
              ->pager('ComNewsChannel.List');

        $this->page = $page;
        $this->sortColumn = $sortColumn;
        $this->sortDirection = $sortDirection;
        return $this->toString();
    }
    
    public function initElement()
    {
        parent::initElement();
        
        $this->addMassAction(
            $this,
            'ajaxDelete', 
            __('Delete', ComNewsChannel::getName()),
            __('Are you realy want to delete selected records ?', ComNewsChannel::getName()),
            'btn-danger'
        );
    }

    public function initColumns()
    {
        $this->view(CMS_Bazalt::getComponent('ComNewsChannel')->View);

        $this->addColumn(new CMS_Form_Element_Column_Checkbox('id'), __('#', ComNewsChannel::getName()))
             ->columnName('n.id')
             ->canSorting(true);

        $this->addColumn(new CMS_Form_Element_Column_Card('title', array(
            'ComNewsChannel.Edit',
            array(
                'id' => 'id'
            ),
            'descField' => 'body',
            'class' => 'jstree-draggable'
        )), __('Title', ComNewsChannel::getName()))
             ->canSorting(true)
             ->columnTemplate('admin/column/card');

        $authors = ComNewsChannel_Model_Article::getAuthors();
        $users = array('-');
        foreach ($authors as $author) {
            $users[$author->id] = $author->getName();
        }

        $this->addColumn(new CMS_Form_Element_Column_User('user_id'), __('User', ComNewsChannel::getName()))
            ->canSorting(true)
            ->filter(CMS_Form_Element_TableColumn::FILTER_SELECT, $users, 'user_id');

        $this->addColumn(new CMS_Form_Element_Column_Date('created_at'), __('Date of creation', ComNewsChannel::getName()))
            ->canSorting(true)
            ->filter(CMS_Form_Element_TableColumn::FILTER_DATE_RANGE)
            ->width(150);

        $this->addColumn(new CMS_Form_Element_Column_Date('updated_at'), __('Date of last edit', ComNewsChannel::getName()))
            ->canSorting(true)
            ->filter(CMS_Form_Element_TableColumn::FILTER_DATE_RANGE);

        $user = CMS_User::getUser();
        if ($user->hasRight('ComNewsChannel', ComNewsChannel::ACL_CAN_MANAGE_NEWS)) {
            $this->addColumn(new CMS_Form_Element_Column_Publish('publish', 'ComNewsChannel_Model_Article'), __('Publish', ComNewsChannel::getName()))
                ->canSorting(true);
        }

        $this->addColumn(new CMS_Form_Element_Column_Actions(array(
            'edit' => array(
                'ComNewsChannel.Edit',
                array(
                    'id' => 'id'
                ),
                'iconClass' => 'icon-pencil',
                'title' => __('Edit', ComNewsChannel::getName())
            ),
            'delete'
        )), __('Actions', ComNewsChannel::getName()))
            ->width(100);
    }
}
