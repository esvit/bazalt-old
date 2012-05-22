<?php

class CMS_Routing_SubmapperRule extends CMS_Routing_Rule
{
    protected function createRoute($url, $params)
    {
        return new CMS_Route($url, $this, $params);
    }

    protected function checkFunctionCondition($url, &$params, $route, $options)
    {
        return CMS_Routing_Rule::checkFunctionConditionForRoute($url, $params, $route, $options);
    }

    public function compare($url, &$params = array())
    {
        $this->getLogger()->info('Find in submapper "' . $this->toUrlPattern() . '" for "' . $url . '"');
        $res = $this->mapper->find($url);
        if ($res) {
            return $res;
        }
        $this->conditions = $this->mapper->Conditions;
        return parent::compare($url, $params);
    }
}