<?php

class Admin_Webservice_Dashboard extends CMS_Webservice_Application
{
    public function saveDashboardOrder($order)
    {
        CMS_Option::set(Admin_Dashboard::DASHBOARD_ORDER_OPTION, $order);
    }
}