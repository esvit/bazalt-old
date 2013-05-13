<?php

namespace Components\News\Model;

class ArticleRefTag extends Base\ArticleRefTag
{
    public static function getMostusedTags($limit = 30)
    {
        $q = ORM::select('\Components\Tags\Model\Tag t')
            ->innerJoin('\Components\NewsChannel\Model\ArticleRefTag a', array('tag_id', 't.id'))
            ->orderBy('t.count DESC')
            ->groupBy('t.id');

        if ($limit != null) {
            $q->limit($limit);
        }

        return $q->fetchAll();
    }

    public static function getTagsCollection()
    {
        $q = ORM::select('\Components\Tags\Model\Tag t')
            ->innerJoin('\Components\NewsChannel\Model\ArticleRefTag a', array('tag_id', 't.id'))
            ->orderBy('t.count DESC')
            ->groupBy('t.id');

        return new CMS_ORM_Collection($q);
    }
}