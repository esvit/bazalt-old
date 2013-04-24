<?php
/**
 * Data model
 *
 * @category DataModel
 * @package  DataModel
 * @author   DataModel Generator v1.3.2 <tools@bazalt.org.ua>
 * @license  http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version  SVN: $Id$
 * @revision SVN: $Rev$
 * @link     http://bazalt.org.ua/
 */

/**
 * Data model for table "com_pages_ref_categories"
 *
 * @category DataModel
 * @package  DataModel
 * @author   DataModel Generator v1.3.2 <tools@bazalt.org.ua>
 * @license  http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link     http://bazalt.org.ua/
 *
 * @property-read mixed PageId
 * @property-read mixed CategoryId
 */
namespace Components\Pages\Model\Base;

abstract class PageRefCategory extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_pages_ref_categories';

    const MODEL_NAME = 'Components\Pages\Model\PagesRefCategories';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('page_id', 'PU:int(10)');
        $this->hasColumn('category_id', 'PU:int(10)');
    }
}
