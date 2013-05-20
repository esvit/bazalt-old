<?php

namespace Components\News\Model\Base;

abstract class Article extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'com_news_news';

    const MODEL_NAME = 'Components\News\Model\Article';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PA:int(10)');
        $this->hasColumn('site_id', 'N:int(10)');
        $this->hasColumn('company_id', 'N:int(10)');
        $this->hasColumn('user_id', 'N:int(10)');
        $this->hasColumn('region_id', 'UN:int(10)');
        $this->hasColumn('category_id', 'UN:int(10)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('body', 'N:longtext');
        $this->hasColumn('publish', 'U:tinyint(1)|0');
        $this->hasColumn('is_top', 'U:tinyint(1)|0');
        $this->hasColumn('comments_number', 'U:int(10)|0');
        $this->hasColumn('url', 'N:varchar(255)');
        $this->hasColumn('item_type', 'U:int(10)|0');
        $this->hasColumn('hits', 'U:int(10)|0');
        $this->hasColumn('source', 'N:varchar(255)');
    }

    public function initRelations()
    {
        $this->hasRelation('User', new \ORM_Relation_One2One('Framework\CMS\User', 'user_id', 'id'));
        $this->hasRelation('Images', new \ORM_Relation_One2Many('Components\News\Model\ArticleImage', 'id', 'news_id'));
        $this->hasRelation('Category', new \ORM_Relation_One2One('Components\News\Model\Category', 'category_id', 'id'));
        $this->hasRelation('Region', new \ORM_Relation_One2One('ComGeo_Model_State', 'region_id', 'id'));

        $this->hasRelation('Tags', new \ORM_Relation_Many2Many('ComTags_Model_Tag', 'news_id', 'Components\News\Model\ArticleRefTag', 'tag_id'));
        $this->hasRelation('Comments', new \ORM_Relation_Many2Many('ComTags_Model_Tag', 'news_id', 'Components\News\Model\ArticleRefTag', 'tag_id'));

        $this->hasRelation('Company', new \ORM_Relation_One2One('ComEnterprise_Model_Company', 'company_id', 'id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Framework\CMS\ORM\Localizable', ['title', 'body']);

        $this->hasPlugin('\ORM_Plugin_Timestampable', array(
            'created' => 'created_at',
            'updated' => 'updated_at'
        ));
    }
}