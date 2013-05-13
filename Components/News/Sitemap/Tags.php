<?php

class ComNewsChannel_Sitemap_Tags extends CMS_Sitemap
{
    public function buildSitemap()
    {
        $collection = ComNewsChannel_Model_ArticleRefTag::getTagsCollection();
        $collection->andWhere('t.count > 5');
        $collection->countPerPage(10);

        $items = $collection->fetchPage();
        $pageCount = $collection->getPagesCount();
        $page = 1;
        while ($pageCount-- > 0) {
            foreach ($items as $item) {
                $url = CMS_Mapper::urlFor('ComNewsChannel.Tag', array('tag' => $item->getAlias()));

                $this->addUrl($url, null, 0.3);
            }
            if ($pageCount != 0) {
                $collection->page(++$page);
                $items = $collection->fetchPage();
            }
        }
    }
}