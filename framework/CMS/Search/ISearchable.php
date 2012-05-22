<?php

interface CMS_Search_ISearchable
{
    static function getSearchCollection();

    function toSearchIndex();

    static function getSearchFields();

    function getSearchType();
}