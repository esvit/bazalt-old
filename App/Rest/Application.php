<?php

namespace App\Rest;

using('Framework.Vendors.Tonic');

use Framework\CMS as CMS;
use Framework\System\Routing\Route;
use Framework\System\Session\Session;

class Application extends CMS\Application
{
    public function init()
    {
        parent::init();

        $components = CMS\Model\Component::getComponentsForSite(CMS\Bazalt::getSiteId());

        $mount = [];
        $load = [
            __DIR__ . '/Webservice/*.php'
        ];
        foreach ($components as $component) {
            $mount['\\Components\\' . $component->name . '\\Webservice'] = '/rest.php/' . strToLower($component->name);
            $load []= 'Components/' . $component->name . '/Webservice/*.php';
        }

        $config = array(
            'load' => $load,
            'mount' => $mount
        );
        $app = new \Tonic\Application($config);

        $request = new \Tonic\Request([
            'uri' => $this->url
        ]);

        // @todo remove
        \Framework\System\Locale\Config::setLocale('en_GB');
        try {
            $resource = $app->getResource($request);

            $response = $resource->exec();

        } catch (\Tonic\NotFoundException $e) {
            $response = new \Tonic\Response(404, $e->getMessage());

        } catch (\Tonic\UnauthorizedException $e) {
            $response = new \Tonic\Response(401, $e->getMessage());
            $response->wwwAuthenticate = 'Basic realm="My Realm"';

        } catch (\Tonic\Exception $e) {
            $response = new \Tonic\Response($e->getCode(), $e->getMessage());
        } catch (CMS\Exception\AccessDenied $e) {
            $response = new \Tonic\Response(403, 'AccessDenied');
        }
        $response->output();
        exit;
    }
}