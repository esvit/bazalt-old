<?php

namespace Components\AdminPanel\Webservice;

use Framework\CMS as CMS;
use Framework\System\Data as Data;

/**
 * @uri /adminpanel/widgets
 * @uri /adminpanel/widgets/:id
 */
class Widgets extends CMS\Webservice\Rest
{
    /**
     * @method PUT
     * @provides application/json
     * @json
     * @return CMS\Webservice\Response
     */
    public function createAlbum()
    {
        $data = new Data\Validator((array)$this->request->data);
        
        $id = (int) $data->getData('id');
        $origWidget = CMS\Model\Widget::getById($id);
        if (!$origWidget) {
            throw new \Exception('Widget with id "' . $id . '" not found');
        }
        $widget = CMS\Model\WidgetInstance::create();
        $widget->widget_id = $origWidget->id;
        $widget->name = $origWidget->title;
        $widget->template = $data->getData('template');
        $widget->position = $data->getData('position');
        $widget->order = 0;
        $widget->save();

        $sorting = str_replace('bz-widget[]=new', 'bz-widget[]=' . $widget->id, $data->getData('sorting'));

        $this->changeOrder($widget->template, $widget->position, $sorting);

        $content = $widget->getWidgetInstance()->fetch();

        return new CMS\Webservice\Response(200, ['id' => $widget->id, 'content' => $content]);
    }

    /**
     * @method GET
     * @action changeOrder
     * @provides application/json
     * @json
     * @return CMS\Webservice\Response
     */
    public function changeOrder($template = null, $position = null, $order = null)
    {
        if (isset($_GET['template'])) {
            $template = $_GET['template'];
        }
        if (isset($_GET['position'])) {
            $position = $_GET['position'];
        }
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        if (empty($order)) {
            return new CMS\Webservice\Response(200, null);
        }
        parse_str($order, $output);
        if (!isset($output['bz-widget']) && !is_array($output['bz-widget'])) {
            throw new \Exception('Invalid orders');
        }
        $orders = $output['bz-widget'];

        foreach ($orders as $pos => $id) {
            $widget = CMS\Model\WidgetInstance::getById((int)$id);
            if (!$widget) {
                throw new \Exception('Widget with id "' . $id . '" not found');
            }
            $widget->template = $template;
            $widget->position = $position;
            $widget->order = $pos;
            $widget->save();
        }
        return new CMS\Webservice\Response(200, true);
    }

    /**
     * @method GET
     * @action getSettings
     * @provides application/json
     * @json
     * @return CMS\Webservice\Response
     */
    public function getSettings($id)
    {
        $widgetInstance = CMS\Model\WidgetInstance::getById((int)$id);
        if (!$widgetInstance) {
            throw new \Exception('Widget with id "' . $id . '" not found');
        }
        $widgetObject = $widgetInstance->getWidgetInstance();

        $content = $widgetObject->getConfigPage();

        $view = $widgetInstance->getWidgetInstance()->view();
        $content .= $view->fetch('cms/widgets/widget_settings');

        // @todo replace this shit
        $folders = $widgetObject->view()->folders();
        $folders []= __DIR__ . PATH_SEP . 'views';
        $folders []= SITE_DIR . '/themes' . PATH_SEP . 'default/views';
        $widgetObject->view()->folders($folders);
        // @endtodo

        $widget = $widgetInstance->Widget;
        $baseName = $widget->default_template;
        $templs = $widgetObject->view()->findTemplates($baseName . '/*');
        $templates = [
            [
                'template' => $widget->default_template,
                'type' => 'Default'
            ]
        ];
        foreach ($templs as $key => $template) {
            $templates[] = [
                'template' => ltrim($template['file'], '/'),
                'type' => 'Theme'
            ];
        }

        // @todo remove
        $w = $widgetInstance->toArray();

        $w['publish'] = $w['publish'] == '1';
        return new CMS\Webservice\Response(200, [
            'widget' => $w,
            'content' => $content,
            'templates' => $templates
        ]);
    }

    /**
     * @method GET
     * @provides application/json
     * @json
     * @return CMS\Webservice\Response
     */
    public function get()
    {
        $widgets = CMS\Model\Widget::getActiveWidgets();

        return new CMS\Webservice\Response(200, $widgets);
    }

    /**
     * @method DELETE
     * @provides application/json
     * @json
     * @return CMS\Webservice\Response
     */
    public function deleteWidget($id)
    {
        $user = CMS\User::get();
        $widget = CMS\Model\WidgetInstance::getById($id);
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $widget->delete();
        return new CMS\Webservice\Response(200, true);
    }

    /**
     * @method POST
     * @provides application/json
     * @json
     * @return CMS\Webservice\Response
     */
    public function saveWidget($id)
    {
        $data = (array)$this->request->data;

        $user = CMS\User::get();
        $widget = CMS\Model\WidgetInstance::getById((int)$data['id']);
        /*if ($user->isGuest()) {
            return new Response(200, null);
        }*/
        $widget->config = (array)$data['config'];
        $widget->widget_template = $data['widget_template'];
        $widget->publish = $data['publish'];
        $widget->save();
        
        $result = $widget->toArray();
        $result['content'] = $widget->getWidgetInstance()->fetch();
        return new CMS\Webservice\Response(200, $result);
    }
}