<?php

class CMS_Search_Collection extends CMS_ORM_Collection
{
    protected $client = null;

    protected $indexes = array();

    protected $result = null;

    protected $query = null;

    protected $totalTime = array();

    protected $total = array();

    protected $totalFound = array();

    protected $words = array();
            
    protected $excerptOption = array();

    protected $objects = array();

    protected $sort = null;

    protected $pagesCount = 1;

    protected $count = 0;

    protected $currentPage = null;

    protected $countPerPage = 20;

    public function __construct()
    {
        using('Framework.System.Sphinx');

        $sphinx = Sphinx_Config::Singleton();
        $this->client = $sphinx->getClient();
        $this->client->setMatchMode(SPH_MATCH_ALL);
        $this->client->setRankingMode(SPH_RANK_EXPR, "sum(lcs*user_weight)*1000+bm25");//(SPH_RANK_PROXIMITY_BM25);
        $this->client->setArrayResult(true);

        $this->excerptOption = array(
            'before_match' => '<em>',
            'after_match' => '</em>',
            'chunk_separator' => ' ... ',
            'limit' => 120,
            'around' => 3,
            'exact_phrase' => false,
            'html_strip_mode' => 'strip',
            'allow_empty' => true,
            'passage_boundary' => 'paragraph'
        );
    }

    public function find($query)
    {
        $this->query = $query;
        return $this;
        //$this->client->SetIndexWeights(array('CMS_Model_User' => 1, 'ComArticlesArticles' => 10, 'ComOrginfoOrganization' => 2));
    }

    public function from($indexes = array())
    {
        $this->indexes = $indexes;
        return $this;
    }

    public function whereGeo($fLat, $fLng, $lat, $lng)
    {
        $this->client->setGeoAnchor($fLat, $fLng, $lat, $lng);
        return $this;
    }

    public function where($attribute, $values = array(), $exclude = false)
    {
        if (!is_array($values)) {
            $values = array($values);
        }
        $this->client->setFilter($attribute, $values, $exclude);
        return $this;
    }

    public function orderBy($order)
    {
        $this->sort = $order;
        $this->client->setSortMode(SPH_SORT_EXTENDED, $order);
        return $this;
    }

    public function execute()
    {
        foreach ($this->indexes as $i => $index) {
            $this->client->addQuery($this->query, $index);
        }
        return $this->client->runQueries();
    }

    public function page($page = null)
    {
        if ($page != null) {
            $this->currentPage = $page;
            return $this;
        }
        if ($this->currentPage === null && isset($_GET['page']) && is_numeric($_GET['page'])) {
            $this->currentPage = (int)$_GET['page'];
        }
        return $this->currentPage;
    }

    public function countPerPage($countPerPage = null)
    {
        if ($countPerPage != null) {
            $this->countPerPage = $countPerPage;
            return $this;
        }
        return $this->countPerPage;
    }

    public function fetchPage()
    {
        $page = $this->page();
        if (!$page || $page < 0) {
            $page = 1;
        }
        $this->client->setLimits(($page - 1) * $this->countPerPage, $this->countPerPage);
        return $this->fetchAll();
    }
    
    public function getPagesCount()
    {
        return $this->pagesCount;
    }

    public function count($count = null)
    {
        if ($count != null) {
            $this->count = $count;
            return $this;
        }
        return $this->count;
    }

    public function fetch()
    {
        $this->client->SetLimits(1, 1);

        return current($this->fetchAll());
    }

    public function fetchAll()
    {
        $results = $this->execute();
        return $this->fetchSearchResult($results);
    }

    protected function fetchSearchResult($results)
    {
        $matches = array();
        $ids = array();

        $this->total = array();
        $this->totalFound = array();
        $this->totalTime = array();
        $this->words = array();

        foreach ($results as $i => $result) {
            $indexName = $this->indexes[$i];
            if ($result['status'] != '0') {
                throw new Exception($result['error']);
            }

            $this->total[$indexName] = (int)$result['total'];
            $this->totalFound[$indexName] = (int)$result['total_found'];
            $this->totalTime[$indexName] = (float)str_replace(',', '.', $result['time']);
            $this->count += (int)$result['total_found'];
            $this->pagesCount = ceil($this->count / $this->countPerPage);

            foreach ($result['matches'] as $match) {
                if (!isset($ids[$indexName])) {
                    $ids[$indexName] = array();
                }
                $match['index'] = $indexName;
                $match['attrs']['@weight'] = $match['weight'];
                $ids[$indexName][] = $match['id'];
                $matches []= $match;
            }

            foreach ($result['words'] as $word => $info) {
                if (isset($this->words[$word])) {
                    $this->words[$word]['docs'] += $info['docs'];
                    $this->words[$word]['hits'] += $info['hits'];
                } else {
                    $this->words[$word] = $info;
                }
            }
        }
    //print_r($matches);exit;
        //usort($matches, array(__CLASS__, 'sortMatches'));
        $this->result = $matches;
        $this->associateMatches($ids);
        return $this->objects;
    }

    protected function associateMatches($ids)
    {
        $results = array();
        foreach ($ids as $indexName => $ids) {
            $q = ORM::select($indexName)
                    ->whereIn('id', $ids);

            $res = $q->fetchAll();

            $results[$indexName] = array();
            foreach ($res as $item) {
                $results[$indexName][$item->id] = $item;

                $itemArr = $item->toSearchIndex();

                $snipets = $this->client->BuildExcerpts($itemArr, $indexName, $this->query, $this->excerptOption);
                $i = 0;
                foreach($itemArr as $field => $value) {
                    $item->{'search_' . $field} = str_replace('', '<br />', $snipets[$i++]); // replace  to new line
                }
            }
        }

        $this->objects = array();
        foreach ($this->result as $i => $match) {
            $indexName = $match['index'];
            $id = $match['id'];

            $item = $results[$indexName][$id];
            $item->search_weight = $match['weight'];
            if (isset($match['attrs']['@geodist'])) {
                $match['attrs']['@geodist'] = (float)$match['attrs']['@geodist'];
                $item->search_geodist = $match['attrs']['@geodist'];
            }

            foreach($match['attrs'] as $field => $value) {
                $item->{$field} = $value;
            }

            $this->result[$i]['object'] = $item;
            $this->objects[$item->id] = $item;
        }
        $this->sortResults();
    }

    protected function sortResults()
    {
        if (!empty($this->sort)) {
            $clause = preg_replace('/,\s+/', ', ->', $this->sort);

            DataType_Array::sortBy($this->objects, '->' . $clause);
        }
    }

    public static function sortMatches($a, $b)
    {
        $weightA = $a['weight'];
        $weightB = $b['weight'];

        if ($weightA == $weightB) {
            return 0;
        }
        return ($weightA < $weightB) ? 1 : -1;
    }
}