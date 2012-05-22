<?php

function smarty_function_hook($params, &$smarty)
{
    $name = $params['name'];

    unset($params['name']);

    if (STAGE == DEVELOPMENT_STAGE) {
        echo '<!-- Hook "' . $name . '" -->';
    }

    Event::trigger('Hooks', $name, array($params));
}