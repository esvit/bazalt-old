<?php

namespace Framework\CMS\Model\Base;

/**
 * Data model for table "cms_languages"
 *
 * @category  DataModels
 * @package   DataModel
 * @author    Bazalt CMS (http://bazalt-cms.com/)
 * @version   Release: $Revision$
 *
 * @property-read int    $id
 * @property-read string $title
 * @property-read string $alias
 * @property-read string $ico
 */
abstract class Language extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_languages';

    const MODEL_NAME = 'Framework\CMS\Model\Language';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:varchar(2)');
        $this->hasColumn('title', 'varchar(50)');
        $this->hasColumn('ico', 'varchar(5)');
    }
}