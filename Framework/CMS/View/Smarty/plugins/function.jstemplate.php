<?php

function smarty_function_jstemplate($params, &$smarty)
{
    $file = $params['file'];
    $content = '';
    foreach($smarty->template_dir as $dir) {
        $fName = realpath($dir).PATH_SEP.$file;
        if(file_exists($fName)) {
            $content = file_get_contents($fName);
            break;
        }
    }
    return ' <script id="'.$params['id'].'" type="text/x-jquery-tmpl">'."\n".$content."\n".'</script>';
}