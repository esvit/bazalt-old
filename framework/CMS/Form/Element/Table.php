<?php

class CMS_Form_Element_Table extends Html_ContainerElement
{
    protected $collection = null;

    protected $pager = null;

    protected $pagerParams = array();

    protected $pagerCounts = array(10, 25, 50, 100);

    protected $massActions = array();

    protected $page = null;

    protected $pageCount = 10;

    protected $sortColumn = null;

    protected $sortDirection = 'up';

    protected $columns = array();

    public function __construct($name, $attributes = array())
    {
        parent::__construct($name, $attributes);

        $this->template('cms/table/base');
        $this->javascriptTemplate('cms/table/javascript/table');

        $this->view(CMS_Application::current()->View);
    }

    public function prependsName()
    {
        return false;
    }

    public function collection(CMS_ORM_Collection $collection = null)
    {
        if ($collection !== null) {
            $this->collection = $collection;
            return $this;
        }
        return $this->collection;
    }

    public function pager($pager = null, $params = array())
    {
        if ($pager !== null) {
            $this->pager = $pager;
            $this->pagerParams = $params;
            return $this;
        }
        return $this->pager;
    }

    public function pagerCounts($pagerCounts = null)
    {
        if ($pagerCounts !== null) {
            $this->pagerCounts = $pagerCounts;
            return $this;
        }
        return $this->pagerCounts;
    }

    public function addMassAction($el, $callback, $title, $confirm = '')
    {
        $callback = str_replace('ajax', '', $callback);
        $this->massActions[$callback] = array(
            'column' => ($el instanceof CMS_Form_Element_TableColumn) ? $column->columnName() : null,
            'callback' => $callback,
            'title' => $title,
            'confirm' => $confirm
        );
    }

    public function addColumn($column, $title)
    {
        if (is_string($column)) {
            $column = new CMS_Form_Element_TableColumn($column, $title);
        }
        $column->table($this)
               ->title($title);
        $this->columns[$column->name()] = $column;

        $this->addElement($column);

        return $column;
    }

    public function initColumns()
    {
    }

    public function initElement()
    {
        if ($this->isInited) {
            return;
        }
        $this->isInited = true;
        parent::initElement();

        $this->initColumns();
        foreach($this->columns as $column) {
            $column->initElement();
        }
    }

    public function toString()
    {
        $this->initElement();

        $this->view->assign('isAjax', Html_Ajax_Form::isAjax());
        $this->view->assign('massActions', $this->massActions);
        $this->view->assign('tableColumns', $this->columns);

        if ($this->collection) {
            if ($this->sortColumn != null) {
                $arr = array_values($this->columns);
                $sortColumn = $arr[$this->sortColumn];

                if ($sortColumn->canSorting()) {
                    $sortColumn->sorting($this->sortDirection);
                    $this->collection->orderBy($sortColumn->columnName() . ' ' . (($this->sortDirection == 'up') ? 'DESC' : 'ASC'));
                }
            }

            $data = $this->collection->getPage($this->page);

            $this->view->assign('pager', $this->collection->getPager($this->pager, $this->pagerParams));
            $this->view->assign('tableData', $data);
        }

        $this->view->assign('element', $this);
        return parent::toString();
    }

    public function showPager($quantity = 9)
    {
        $page = $this->collection->page();
        $countPage = $this->collection->getPagesCount();

        $pagerMiddle = ceil($quantity / 2);
        // first is the first page listed by this pager piece (re quantity)
        $pagerFirst = $page - $pagerMiddle + 1;
        // last is the last page listed by this pager piece (re quantity)
        $pagerLast = $page + $quantity - $pagerMiddle;

        $i = $pagerFirst;
        if ($pagerLast > $countPage) {
            // Adjust "center" if at end of query.
            $i = $i + ($countPage - $pagerLast);
            $pagerLast = $countPage;
        }
        if ($i <= 0) {
            // Adjust "center" if at start of query.
            $pagerLast = $pagerLast + (1 - $i);
            $i = 1;
        }

        if ($countPage > 1) {
            $items []= array(
                'role' => 'pager-prev',
                'url'  => $this->getUrl($page - 1),
                'enable' => ($page > 1)
            );
            if ($page > $pagerMiddle) {
                $items []= array(
                    'role' => 'pager-item',
                    'url'  => $this->getUrl(1),
                    'page' => 1
                );
            }

            // When there is more than one page, create the pager list.
            if ($i != $countPage) {
                if ($i > 2) {
                    $items []= array(
                        'role' => 'pager-ellipsis'
                    );
                }

                // Now generate the actual pager piece.
                for (; $i <= $pagerLast && $i <= $countPage; $i++) {
                    if ($i < $page) {
                        $items[] = array(
                            'role' => 'pager-item',
                            'url'  => $this->getUrl($i),
                            'page' => $i
                        );
                    }
                    if ($i == $page) {
                        $items[] = array(
                            'role' => 'pager-current',
                            'url'  => $this->getUrl($i),
                            'page' => $i
                        );
                    }
                    if ($i > $page) {
                        $items[] = array(
                            'role' => 'pager-item',
                            'url'  => $this->getUrl($i),
                            'page' => $i
                        );
                    }
                }

                if ($i < $countPage) {
                    $items[] = array(
                        'role' => 'pager-ellipsis'
                    );
                }
            }

            if ($page - 1 < ($countPage - $pagerMiddle)) {
                $items[] = array(
                    'role' => 'pager-item',
                    'url'  => $this->getUrl($countPage),
                    'page' => $countPage
                );
            }
            $items []= array(
                'role' => 'pager-next',
                'url'  => $this->getUrl($page + 1),
                'enable' => ($page < $countPage),
                'page' => $page + 1
            );

            $this->view->assign('pages', $items);
            // print_r( $items);
            return $this->view->fetch('cms/table/pager');
        }
    }

    public function ajaxGetPage($page, $sortColumn, $sortDirection)
    {
        $this->page = $page;
        $this->sortColumn = $sortColumn;
        $this->sortDirection = $sortDirection;

        return $this->toString();
    }
    
    public function ajaxDelete($ids)
    {
    }
}