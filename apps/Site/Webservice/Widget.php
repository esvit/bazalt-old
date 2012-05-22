<?php

class Site_Webservice_Widget extends CMS_Webservice_Application
{
    public function changePosition($template, $position, $order)
    {
        if (empty($order)) {
            return;
        }
        parse_str($order, $output);
        if (!isset($output['cms-widget']) && !is_array($output['cms-widget'])) {
            throw new Exception('Invalid orders');
        }
        $orders = $output['cms-widget'];

        foreach ($orders as $pos => $id) {
            $widget = CMS_Model_WidgetInstance::getById((int)$id);
            if (!$widget) {
                throw new Exception('Widget with id "' . $id . '" not found');
            }
            $widget->template = $template;
            $widget->position = $position;
            $widget->order = $pos;
            $widget->save();
        }
        return $orders;
    }

    public function deleteInstance($id)
    {
        $widget = CMS_Model_WidgetInstance::getById((int) $id);
        if (!$widget) {
            throw new Exception('Widget with id "' . $id . '" not found');
        }
        $widget->delete();
    }

    public function createInstance($id, $template, $config, $pos, $order = 1)
    {
        $origWidget = CMS_Model_Widget::getById(intval($id));
        if (!$origWidget) {
            throw new Exception('Widget with id "' . $id . '" not found');
        }
        parse_str($config, $config);
        unset($config['widget_id']);
        $widget = CMS_Model_WidgetInstance::create();
        $widget->widget_id = $origWidget->id;
        $widget->name = $origWidget->title;
        $widget->config = serialize($config);
        $widget->template = $template;
        $widget->position = $pos;
        $widget->order = $order;

        $widget->save();

        $content = $widget->getWidgetInstance()->fetch();

        return array( 
            'widget' => $widget->toArray(),
            'html' => $content
        );
    }
    
    public function widgets($page = 1)
    {
        $widgetsCollection = CMS_Model_Widget::getActiveCollection();

        return $widgetsCollection->page($page);
    }

    public function saveWidgetSettings($id, $settings)
    {
        $widget = CMS_Model_WidgetInstance::getById((int)$id);
        if (!$widget) {
            throw new Exception('Widget with id "' . $id . '" not found');
        }
        $params = array();
        foreach ($settings as $setting) {
            $params[$setting->name] = $setting->value;
        }
        $template = $params['cms_widget_template'];
        $widget->widget_template = $template;
        
        unset($params['widget_id']);
        unset($params['cms_widget_template']);
        $widget->config = $params;
        $widget->save();

        $content = $widget->getWidgetInstance()->fetch();
        
        return array(
            'html' => $content
        );
    }
    
    public function createCustomTemplate($widgetId, $name, $title, $content)
    {
        $theme = CMS_Model_Theme::getByAlias(CMS_Theme::getCurrentTheme()->Alias);
        if(!$theme) {
            throw new Exception(sprintf('Theme "%s" not found', CMS_Theme::getCurrentTheme()->Alias));
        }
        $file = CMS_Theme::getCurrentTheme()->getTemplatesPath() . '/' . $name;
        
        if(file_put_contents($file, $content) === false) {
            throw new Exception(sprintf('Cannot write "%s" template file', $file));
        }
        
        $t = new CMS_Model_WidgetTemplates();
        $t->theme_id = $theme->id;
        $t->widget_id = $widgetId;
        $t->name = $name;
        $t->title = $title;
        $t->save();
        return true;
    }
    
    public function saveCustomTemplate($name, $content)
    {
        $theme = CMS_Model_Theme::getByAlias(CMS_Theme::getCurrentTheme()->Alias);
        if(!$theme) {
            throw new Exception(sprintf('Theme "%s" not found', CMS_Theme::getCurrentTheme()->Alias));
        }
        $file = CMS_Theme::getCurrentTheme()->getTemplatesPath() . '/' . $name;
        
        if(file_put_contents($file, $content) === false) {
            throw new Exception(sprintf('Cannot write "%s" template file', $file));
        }
    }
    
    public function getCustomTemplateContent($name)
    {
        $theme = CMS_Model_Theme::getByAlias(CMS_Theme::getCurrentTheme()->Alias);
        if(!$theme) {
            throw new Exception(sprintf('Theme "%s" not found', CMS_Theme::getCurrentTheme()->Alias));
        }
        $file = CMS_Theme::getCurrentTheme()->getTemplatesPath() . '/' . $name;
        return file_get_contents($file);
    }
}
