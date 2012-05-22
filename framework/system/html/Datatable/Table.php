<?php

class Html_Datatable_Table extends Html_FormElement
{
    protected $echoNum = 0;

    protected $columns = array();

    protected $sortingFields = array();

    protected $pageCount = 10;

    protected $pageNum = 1;

    protected $columnsCount = 0;

    protected $dataCollection = null;

    protected $rowCallback = array();

    protected $redrawCallback = array();

    protected $ajaxCallback = array();

    protected $sSearch = null;
    
    protected $bJQueryUI = true;
    
    protected $oLanguage = null;

    public function renderHeader()
    {
        $str = '';
        if (count($this->columns) > 0) {
            foreach ($this->columns as $column) {
                $str .= $column->renderHeader();
            }
        }
        return $str;
    }

    public function renderFooter()
    {
        $str = '';
        if (count($this->columns) > 0) {
            foreach ($this->columns as $column) {
                $str .= $column->renderFooter();
            }
        }
        return $str;
    }
    
    public function useJQueryUI($use)
    {
        $this->bJQueryUI = $use;
    }

    public static function jsEscape($str)
    {
        return /*addcslashes(*/json_encode($str);//,"\\\'\"\n\r");
    }

    public function setDataCollection($collection)
    {
        $this->dataCollection = $collection;
    }

    public function addRowCallback($js)
    {
        $this->rowCallback []= $js;
    }

    public function addAjaxCallback($js)
    {
        $this->ajaxCallback []= $js;
    }

    public function addRedrawCallback($js)
    {
        $this->redrawCallback []= $js;
    }

    public function addColumn(Html_Datatable_Column &$column)
    {
        if (!$column instanceof Html_Datatable_Column) {
            throw new Exception('Invalid column type');
        }
        $column->container = $this;
        $column->index = count($this->columns);

        if (!$column instanceof Html_Datatable_Column_Hidden) {
            $this->columnsCount++;
        }
        $column->realIndex = $this->columnsCount;
        $this->columns []= $column;

        return $column;
    }

    protected function getColumns()
    {
        $countColumns = intval($_GET['iColumns']);
    }

    protected function checkPostBack()
    {
        //print_r($_GET);
        if (isset($_REQUEST['iDisplayLength'])) {
            $this->pageCount = (int)$_REQUEST['iDisplayLength'];
            if ($this->pageCount > 100 || $this->pageCount < 10) {
                $this->pageCount = 10;
            }
        }

        if (isset($_REQUEST['iDisplayStart'])) {
            $this->pageNum = (int)$_REQUEST['iDisplayStart'];
            $this->pageNum = ($this->pageNum / $this->pageCount) + 1;
        }

        if (isset($_REQUEST['iSortingCols']) && is_numeric($_REQUEST['iSortingCols'])) {
            $cols = intval($_REQUEST['iSortingCols']);

            for ($i = 0; $i < $cols; $i++) {
                $column = 'iSortCol_' . $i;
                $column = $_REQUEST[$column];
                $direction = ($_REQUEST['sSortDir_' . $i] == 'asc') ? 'ASC' : 'DESC';

                $sortColumn = $this->columns[$column];
                if ($sortColumn->sortable && !empty($sortColumn->name)) {
                    $this->sortingFields [] = '`' . $sortColumn->name . '` ' . $direction;
                }
            }
        }
        if (isset($_REQUEST['sSearch']) && !empty($_REQUEST['sSearch'])) {
            $this->sSearch = $_REQUEST['sSearch'];
        }

        if (isset($_REQUEST['sEcho'])) {
            $this->echoNum = $_REQUEST['sEcho'];
            return true;
        }
        return false;
    }

    protected function createAjaxResponse($collection)
    {
        $result = new stdClass();

        $result->sEcho = $this->echoNum;
        $result->iTotalRecords = $collection->getCount();
        $result->iTotalDisplayRecords = $collection->getCount();
        $result->aaData = array();

        return $result;
    }

    protected function echoData()
    {
        $col = $this->dataCollection;

        if (count($this->sortingFields) > 0) {
            $col->addOrderBy(implode(',', $this->sortingFields));
        }
        if ($this->sSearch != null) {
            $col->andWhereGroup();
            foreach ($this->columns as $column) {
                if ($column->searchable) {
                    $column->addSearchCondition($col, $this->sSearch);
                }
            }
            $col->endWhereGroup();
        }

        $data = $col->getPage($this->pageNum, $this->pageCount);

        $result = $this->createAjaxResponse($col);

        foreach ($data as $item) {
            $dataItem = array();
            foreach ($this->columns as $column) {
                $dataItem []= $column->getData($item);
            }
            $result->aaData []= $dataItem;
        }
        return json_encode($result);
    }

    protected function getSortingJs()
    {
        $sort = array();
        foreach ($this->columns as $column) {
            if ($column->sorting != null) {
                $sort []= '[' . $column->index . ',"' . $column->sorting . '"]';
            }
        }
        if (count($sort) > 0) {
            $js = '[' . implode(',', $sort) . ']';
        }
        return $js;
    }

    protected function beforeRender()
    {
    }

    public function toString()
    {
        $this->beforeRender();

        Scripts::addModule('DataTables');

        $str = '';
        if ($this->checkPostBack()) {
            //header('Content-type: application/json; charset=UTF-8');
            if (count($this->columns) > 0) {
                echo $this->echoData();
            }
            exit;
        }

        $attrs = $this->getAttributesString();

        $cls = 'bz-form-row';
        if (count($this->errors) > 0) {
            $cls .= ' bz-form-row-has-error';
        }
        $str .= '<div class="ui-grid ui-widget ui-widget-content ui-corner-all">';
        $str .= '   <div class="ui-grid-loading ui-corner-all"></div>';
        $str .= '<table cellpadding="0" cellspacing="0" border="0" class="display" id="' . $this->id() . '">';
        $str .= '<thead>';
        $str .= '		<tr>';
        $str .= $this->renderHeader();
        $str .= '		</tr>';
        $str .= '</thead>';
        $str .= '<tbody>';
        $str .= '	<tr>';
        $str .= '		<td colspan="' . count($this->columns) . '" class="dataTables_empty">Loading data from server</td>';
        $str .= '	</tr>';
        $str .= '</tbody>';
        $str .= '<tfoot>';
        $str .= '	<tr>';
        $str .= $this->renderFooter();
        $str .= '	</tr>';
        $str .= '</tfoot>';
        $str .= '</table>';
        $str .= '</div>';

        $columns = '';
        if (count($this->columns) > 0) {
            $columns .= '"aoColumnDefs" : [' . "\n";
            foreach ($this->columns as $column) {
                $columns .= $column->toString() . ',';
            }
            $columns = substr($columns, 0, -1);
            $columns .= '],' . "\n";
        }

        $tr =  '';
        if($this->oLanguage) {
            $tr =  "oLanguage: ".json_encode($this->oLanguage).',';
        }

        $js = '$.oTable = $("#' . $this->id() . '").dataTable( {' . "\n";
        $js .= $tr;
        $js .= '	"bProcessing": true,' . "\n";
        $js .= '	"bJQueryUI": '.($this->bJQueryUI == true ? 'true' : 'false').',' . "\n";
        $js .= '	"bStateSave" : true,' . "\n";
        $js .= '	"sPaginationType": "full_numbers",' . "\n";
        $js .= '	"bServerSide": true,' . "\n";
        $js .= '	"sDom": \'<"H"<"toolbar ui-float-left">fr>t<"F"lpi>\',' . "\n";

        $sorting = $this->getSortingJs();
        if ($sorting != null) {
            $js .= '	"aaSorting": ' . $sorting . ',' . "\n";
        }

        if (count($this->rowCallback) > 0) {
            $js .= '"fnRowCallback": function( nRow, aData, iDisplayIndex ) { $(nRow).data("data", aData); ' . "\n";

            $js .= implode("\n", $this->rowCallback);

            $js .= 'return nRow; },' . "\n";
        }

        if (count($this->redrawCallback) > 0) {
            $js .= '"fnDrawCallback": function() {' . "\n";

            $js .= implode("\n", $this->redrawCallback);

            $js .= '},' . "\n";
        }


        $js .= '    "sAjaxSource": "' . Url::getRequestUrl() . '",' . "\n";

        $js .= $columns;

        $js .= '    "fnServerData": function ( sSource, aoData, fnCallback ) {' . "\n";
        $js .= '        if ($(".ui-filter-by-type .active").size() > 0) {' . "\n";
        $js .= '            var id = $(".ui-filter-by-type .active").attr("id").slice(6);';
        $js .= '            aoData.push( { "name" : "filter", "value": id } );' . "\n";
        $js .= '        }' . "\n";
        $js .= '        $(".ui-grid-loading").show(); ' . "\n";
        $js .= '        $.getJSON( sSource, aoData, function (json) { ' . "\n";
        $js .= '            $(".ui-grid-loading").hide(); ' . "\n";
        $js .= implode("\n", $this->ajaxCallback);
        $js .= '            fnCallback(json)' . "\n";
        $js .= '        } );' . "\n";
        $js .= '    }' . "\n";
        $js .= '} ).fnSetFilteringDelay(500);';

        Html_Form::addOnReady($js);

        return $str;
    }
}
