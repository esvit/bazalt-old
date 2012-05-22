<?php

class Admin_Dashboard
{
    const DASHBOARD_ORDER_OPTION = 'CMS.DashboardOrder';

    protected static $instance = null;

    protected $blocks = array();

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    public function addBlock(Admin_Dashboard_Block $block)
    {
        if (!($block instanceof Admin_Dashboard_Block)) {
            throw new Exception('Invalid dashboard block type, must be Admin_Dashboard_Block');
        }

        $this->blocks []= $block;
    }

    public function generateDashboard()
    {
        $order = CMS_Option::get(self::DASHBOARD_ORDER_OPTION, null);
        parse_str($order, $output);
        $column1 = isset($output['column1']) ? array_flip(explode(',', $output['column1'])) : array();
        $column2 = isset($output['column2']) ? array_flip(explode(',', $output['column2'])) : array();

        $columns1 = array();
        $columns2 = array();
        $num = 1;
        foreach ($this->blocks as $name => &$block) {
            $block->id = 'dashboard_' . $name;
            $block->html = $block->getContent();

            $order = null;
            if (array_key_exists($block->id, $column1)) {
                $order = (int)$column1[$block->id];
                $num = 1;
            } else if (array_key_exists($block->id, $column2)) {
                $order = (int)$column2[$block->id];
                $num = 2;
            } else {
                $num = ($num == 1) ? 2 : 1;
            }
            if ($num == 1) {
                if ($order == null) {
                    $order = count($columns1);
                }
                $columns1[$order] = $block;
            } else {
                if ($order == null) {
                    $order = count($columns2);
                }
                $columns2[$order] = $block;
            }
        }

        ksort($columns1);
        ksort($columns2);
        return array(
            'column1' => $columns1,
            'column2' => $columns2
        );
    }
}