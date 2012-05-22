<?php

function smarty_function_jsservice($params, &$smarty)
{
    Scripts::addService($params['name']);
}