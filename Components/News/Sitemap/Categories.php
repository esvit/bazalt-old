<?php

class ComNewsChannel_Sitemap_Categories extends CMS_Sitemap
{
    public function buildSitemap()
    {
        $collection = ComNewsChannel_Model_Category::getCategoriesCollection();

        $collection->countPerPage(10);

        $items = $collection->fetchPage();
        $pageCount = $collection->getPagesCount();
        $page = 1;
        while ($pageCount-- > 0) {
            foreach ($items as $item) {
                $url = CMS_Mapper::urlFor('ComNewsChannel.ShowCategory', array('category' => $item->Elements));

                $this->addUrl($url);
            }
            if ($pageCount != 0) {
                $collection->page(++$page);
                $items = $collection->fetchPage();
            }
        }
    }
}