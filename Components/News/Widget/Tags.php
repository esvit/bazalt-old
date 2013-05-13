<?php

class ComNewsChannel_Widget_Tags extends CMS_Widget_Component
{
    public function fetch($vars)
    {
        $tags = ComNewsChannel_Model_ArticleRefTag::getMostusedTags();

        $total = 0;
        $values = array();
        foreach ($tags as &$tag) {
            $total += $tag->count;
            $values [] = $tag->count;
        }

        $minimumCount = min(array_values($values));
        $maximumCount = max(array_values($values));
        $spread = $maximumCount - $minimumCount;

        $spread == 0 && $spread = 1;

        $minFontSize = 1;
        $maxFontSize = 10;
        foreach ($tags as &$tag) {
            $tag->size = round($minFontSize + ($tag->count - $minimumCount) * ($maxFontSize - $minFontSize) / $spread);
        }
        $cnt = (int)ceil(sqrt(count($tags)));
        $tagCloud = array();

        $n = 0;
        $iIndex = $jIndex = $cnt;
        $count = 0;
        while ($n < $cnt) {
            for ($i = 0; $i < $n; $i++) {
                $tagCloud[$iIndex++][$jIndex] = $tags[$count++];
            }
            for ($i = 0; $i < $n; $i++) {
                $tagCloud[$iIndex][$jIndex++] = $tags[$count++];
            }
            $n++;
            for ($i = 0; $i < $n; $i++) {
                $tagCloud[$iIndex--][$jIndex] = $tags[$count++];
            }
            for ($i = 0; $i < $n; $i++) {
                $tagCloud[$iIndex][$jIndex--] = $tags[$count++];
            }
            $n++;
        }
        ksort($tagCloud);
        $tags = array();
        foreach ($tagCloud as &$row) {
            ksort($row);
            foreach ($row as $tag) {
                $tag->url = CMS_Mapper::urlFor('ComNewsChannel.Tag', array('tag' => $tag->getAlias()));
                $tags []= $tag;
            }
        }

        $this->view->assign('tags', $tags);
        $this->view->assign('tagCloud', $tagCloud);

        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        return;
    }
}