<?php

class CMS_Search
{
    public static function search($query, $indexes = null)
    {
        /*$indexes = array('CMS_Model_User', 'ComArticlesArticles', 'ComEnterprise_Model_Company',
                        'ComEnterprise_Model_Office');*/
        $q = new CMS_Search_Collection();

        $q->find($query)
          ->from($indexes);
          //->whereGeo('lat', 'lng', 5, 4)
         //->orderBy('@geodist ASC, @weight DESC');

        return $q;
    }
}