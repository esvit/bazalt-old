<?php

function smarty_function_jsmodule($params, &$smarty)
{
    Scripts::addModule($params['name']);
}