<?php

namespace Framework\CMS\Model\Base;

abstract class Role extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_roles';

    const MODEL_NAME = 'Framework\CMS\Model\Role';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'UN:int(10)');
//        $this->hasColumn('name', 'varchar(255)');
//        $this->hasColumn('description', 'text');
        $this->hasColumn('is_guest', 'U:tinyint(1)|0');
        $this->hasColumn('system_acl', 'U:tinyint(1)|0');
        $this->hasColumn('is_hidden', 'U:tinyint(1)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('Users', new \ORM_Relation_Many2Many('Framework\CMS\User', 'user_id', 'Framework\CMS\RoleRefUser', 'role_id'));
    }

    public function initPlugins()
    {
        /*$this->hasPlugin('CMS_ORM_Localizable', array(
            'fields' => array('name', 'description'),
            'type' => CMS_ORM_Localizable::ROWS_LOCALIZABLE
        ));*/
    }
}