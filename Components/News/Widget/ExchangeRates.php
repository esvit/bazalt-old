<?php

class ComNewsChannel_Widget_ExchangeRates extends CMS_Widget_Component
{
    public static function getRates()
    {
        $date = CMS_Option::get('ComNewsChannel.RatesDate', false);
        $now = new DateTime("now");
        if ($date) {
            $date = new DateTime(date('Y-m-d H:i:s', $date));
            $diff = $now->diff($date);
            if ($diff->days == 0) {
                $rates = unserialize(CMS_Option::get('ComNewsChannel.Rates'));
                if ($rates) {
                    return $rates;
                }
            }
        }

        using('Framework.System.XML');
        $url = 'http://bank-ua.com/export/currrate.xml';
        try {
            $obj = XML_Parser::parse($url);
        } catch (Exception $e) {
            CMS_Option::set('ComNewsChannel.RatesDate', time());
            return null;
        }
        if (!$obj) {
            return null;
        }
        $rates = array();
        foreach ($obj->nodes('item') as $node) {
            $rates[$node->node('char3')->value()] = array(
                'date' => strToTime($node->node('date')->value()),
                'code' => $node->node('code')->value(),
                'size' => $node->node('size')->value(),
                'name' => $node->node('name')->value(),
                'rate' => $node->node('rate')->value(),
                'change' => $node->node('change')->value()
            );
        }
        CMS_Option::set('ComNewsChannel.RatesDate', time());
        CMS_Option::set('ComNewsChannel.Rates', serialize($rates));
        return $rates;
    }

    public function fetch($config)
    {
        $rates = self::getRates();
        if (!$rates) {
            return parent::fetch();
        }
        $date = null;
        foreach ($rates as $code => $rate) {
            $date = $rates[$code]['date'];
            if (!isset($this->options[$code])) {
                unset($rates[$code]);
            } else {
                $rates[$code]['change'] = sprintf('%2d', $rates[$code]['change']);
            }
        }
        $this->view->assign('date', date('Y-m-d H:i:s', $date));
        $this->view->assign('rates', $rates);
        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        $rates = self::getRates();

        $this->view->assign('rates', $rates);
        $this->view->assign('options', $this->options);
        return $this->view->fetch('widgets/exchangerates-settings');
    }

}