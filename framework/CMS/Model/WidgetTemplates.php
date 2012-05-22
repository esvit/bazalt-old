<?php

class CMS_Model_WidgetTemplates extends CMS_Model_Base_WidgetTemplates
{
    public static function getByWidgetAndTheme($widgetId, $themeId)
    {
        $q = CMS_Model_WidgetTemplates::select()
                ->where('widget_id = ?', $widgetId)
                ->andWhere('theme_id = ?', $themeId);
        return $q->fetchAll();
    }
}
