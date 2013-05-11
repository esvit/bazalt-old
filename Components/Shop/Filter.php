<?php

class ComEcommerce_Filter
{
    protected $minPrice = null;

    protected $maxPrice = null;

    protected $fields = array();

    public function __construct($filter = null)
    {
        if ($filter != null ) {
            $filter = is_array($filter) ? $filter : explode(';', $filter);

            foreach ($filter as $item) {
                list($k, $item) = explode('=', $item);
                $this->fields[$k][] = $item;
            }
        }
    }

    public function minPrice($price = null)
    {
        if ($price !== null) {
            $this->minPrice = $price;
            return $this;
        }
        return $this->minPrice;
    }

    public function maxPrice($price = null)
    {
        if ($price !== null) {
            $this->maxPrice = $price;
            return $this;
        }
        return $this->maxPrice;
    }

    public function addFilter($field, $value)
    {
        if (!isset($this->fields[$field])) {
            $this->fields[$field] = array();
        }
        $this->fields[$field][] = $value;
    }

    public function isActive($field, $value)
    {
        return isset($this->fields[$field]) && in_array($value, $this->fields[$field]);
    }

    public function getFilter()
    {
        return $this->fields;
    }

    public function toString($addonFilters = array(), $removeFilter = false, $withPriceMask = false)
    {
        $filter = $this->fields;
        if (count($addonFilters) > 0) {
            foreach ($addonFilters as $filterName => $value) {
                if (!is_array($value)) {
                    $value = array($value);
                }
                if (!is_array($filter[$filterName])) {
                    $filter[$filterName] = array();
                }
                if ($removeFilter) {
                    $filter[$filterName] = array_diff($filter[$filterName], $value);
                } else {
                    $filter[$filterName] = array_merge($filter[$filterName], $value);
                }
            }
        }
        if ($withPriceMask) {
            $filter['price'] = array('$1-$2');
        }
        asort($filter, SORT_STRING);
        $str = '';
        foreach ($filter as $field => $values) {
            $values = array_unique($values, SORT_STRING);
            foreach ($values as $value) {
                $str .= $field . '=' . $value . ';';
            }
        }
        return rtrim($str, ';');
    }

    public function addToCollection(CMS_Search_Query $query, $ignoreFilters = array())
    {
        static $joinNum;

        $query->setFilter('site_id', CMS_Bazalt::getSiteId());

        $fields = array();
        foreach ($this->fields as $k => $item) {
            if (in_array($k, $ignoreFilters)) {
                continue;
            }
            if (is_numeric($k)) {
                $txt = '';
                foreach ($item as $v) {
                    $txt .= ' "' . $k . '_' . $v . '" |';
                }
                $txt = substr($txt, 0, -1);
                $query->addQuery('(@field_terms_attr ' . $txt . ')');
                continue;
            }
            switch (strToLower($k)) {
            case 'brand':
                $query->setFilter('brand_id', $item);
                break;
            case 'price':
                $range = explode('-', $item[0]);
                
                $query->setFilterFloatRange('price', (float)$range[0], (float)$range[1]);
                $this->minPrice((int)$range[0]);
                $this->maxPrice((int)$range[1]);

                break;
            case 'order':
            default:
                throw new Exception('Invalid filter "' . $k . '"');
            }
        }
        return $query;
    }

    public function addToQuery(ORM_Query_Select $q, $ignoreFilters = array())
    {
        static $joinNum;

        foreach ($this->fields as $k => $item) {
            if (in_array($k, $ignoreFilters)) {
                continue;
            }
            if (is_numeric($k)) {
                $q->andWhereGroup();
                if (!$joinNum) {
                    $joinNum = 0;
                }
                foreach ($item as $v) {
                    $q->leftJoin('ComEcommerce_Model_ProductsFields f' . $joinNum, array('product_id', 'p.id'));
                    $q->orWhere('f' . $joinNum . '.value = ? AND f' . $joinNum . '.field_id = ?', array((int)$v, $k));
                    if ($joinNum++ > 2)
                        continue;
                }
                $q->endWhereGroup();
                continue;
            }
            switch (strToLower($k)) {
            case 'brand':
                $q->andWhereGroup();
                foreach ($item as $v) {
                    $q->orWhere('p.brand_id = ?', (int)$v);
                }
                $q->endWhereGroup();
                break;
            case 'price':
                $range = explode('-', $item[0]);
                
                $this->minPrice((int)$range[0]);
                $this->maxPrice((int)$range[1]);
                $q->andWhere('p.price >= ?', (int)$range[0]);
                $q->andWhere('p.price <= ?', (int)$range[1]);
                //$q->andWhere('p.in_stock = ?', 1);

                break;
            case 'order':
                $q->andWhereGroup();
                foreach ($item as $v) {
                    $q->orWhere('p.brand_id = ?', (int)$v);
                }
                $q->endWhereGroup();
                break;
            default:
                throw new Exception('Invalid filter "' . $k . '"');
            }
        }
        return $q;
    }
}