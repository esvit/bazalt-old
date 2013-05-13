<?php

class ComNewsChannel_Sitemap_Archive extends CMS_Sitemap
{
    public function buildSitemap()
    {
        $collection = ComNewsChannel_Model_Article::getCollection(true);
        //$collection->andWhere('created_at <= FROM_UNIXTIME(?)', strtotime('-2 day'));

        $collection->countPerPage(10);

        $items = $collection->fetchPage();
        $pageCount = $collection->getPagesCount();
        $page = 1;
        $domain = self::getDomain();
        while ($pageCount-- > 0) {
            foreach ($items as $item) {
                $url = $item->getUrl(true);

                $url = $this->addUrl($url, strtotime($item->updated_at), 0.7);

                $this->urls[$url]['images'] = array();
                $images = $item->Images->get();
                foreach ($images as $image) {
                    $this->urls[$url]['images'] []= $domain . $image->getThumb('original');
                }
            }
            if ($pageCount != 0) {
                $collection->page(++$page);
                $items = $collection->fetchPage();
            }
        }
    }
}