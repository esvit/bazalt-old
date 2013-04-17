<?php

namespace Framework\CMS\Model\Base;

/**
 * Data model for table "cms_users"
 *
 * @category  CMS
 * @package   DataModel
 *
 * @property-read int $id
 * @property-read varchar $login
 * @property-read varchar $password
 * @property-read varchar $firstname
 * @property-read varchar $secondname
 * @property-read varchar $patronymic
 * @property-read varchar $email
 * @property-read varchar $gender
 * @property-read varchar $birth_date
 * @property-read bool $is_active
 * @property-read varchar $last_activity
 * @property-read varchar $activation_code
 * @property-read bool $is_god
 */
abstract class User extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_users';

    const MODEL_NAME = 'Framework\CMS\Model\User';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('login', 'varchar(255)');
        $this->hasColumn('password', 'varchar(255)');
        $this->hasColumn('firstname', 'varchar(255)');
        $this->hasColumn('secondname', 'varchar(255)');
        $this->hasColumn('patronymic', 'varchar(255)');
        $this->hasColumn('email', 'N:varchar(60)');
        $this->hasColumn('gender', "ENUM('unknown','male','female')|'unknown'");
        $this->hasColumn('birth_date', 'N:date');
        $this->hasColumn('is_active', 'U:tinyint(1)');
        $this->hasColumn('last_activity', 'N:datetime');
        $this->hasColumn('activation_code', 'varchar(50)');
        $this->hasColumn('is_god', 'U:tinyint(1)');
        $this->hasColumn('auth_code', 'varchar(50)');
    }

    public function initRelations()
    {
        $this->hasRelation('Roles', new \ORM_Relation_Many2Many('Framework\CMS\Model\Role', 'user_id', 'Framework\CMS\Model\RoleRefUser', 'role_id'));
        $this->hasRelation('SiteRoles', new \ORM_Relation_Many2Many('Framework\CMS\Model\Role', 'user_id', 'Framework\CMS\Model\RoleRefUser', 'role_id', array('ref.site_id' => \Framework\CMS\Bazalt::getSiteId())));
        $this->hasRelation('Settings', new \ORM_Relation_One2Many('Framework\CMS\Model\UserSetting', 'id', 'user_id'));
        $this->hasRelation('Sites', new \ORM_Relation_Many2Many('Framework\CMS\Model\Site', 'user_id', 'Framework\CMS\Model\SiteRefUser', 'site_id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Framework\System\ORM\Plugin\Timestampable', ['created' => 'reg_date']);
    }
}