<?php

class CMS_Model_ChangeLog extends CMS_Model_Base_ChangeLog
{
    public static function saveLog(Component $component, CMS_Model_User $user, $action, $params = array())
    {
        $log = new CMS_Model_ChangeLog();
        $log->component_id = $component->getCmsComponent()->id;
        if ($user->login) {
            $log->user_id = $user->id;
        } else {
            $log->user_id = null;
        }
        $log->action = $action;
        $log->params = serialize($params);
        $log->save();
    }

    public static function getLastChanges($limit = 10)
    {
        return CMS_Model_ChangeLog::select()
                ->orderBy('timestamp DESC')
                ->limit(intval($limit))
                ->fetchAll();
    }

    public function getComponent()
    {
        return CMS_Bazalt::getComponent($this->Component->name);
    }

    public function getParams()
    {
        $params = unserialize($this->params);
        if ($this->User != null) {
            $userParams = $this->User->toArray();
            foreach ($userParams as $ukey => $uvalue) {
                $params['user_' . $ukey] = $uvalue;
            }
        }
        return $params;
    }

    public function toString()
    {
        $component = $this->getComponent();
        $actions = $component->getLogActions();
        $action = $actions[$this->action];
        $params = $this->getParams();
        return DataType_String::replaceConstants($action, $params);
    }
}