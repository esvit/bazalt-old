<?php

class Routing_SubmapperRule extends Routing_Rule
{
    public function compare($url, &$params = array())
    {
        $this->getLogger()->info('Find in submapper "' . $this->urlPattern . '" for "' . $url . '"');
        $res = $this->mapper->find($url);
        if ($res) {
            return $res;
        }
        $this->conditions = $this->mapper->Conditions;
        return parent::compare($url, $params);
    }
}