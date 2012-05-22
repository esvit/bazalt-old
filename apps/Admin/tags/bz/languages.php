<?php

function bz_languages($content, $args = array())
{
    $languages = CMS_Language::getLanguages();
    $currLanguage = CMS_Language::getCurrentLanguage();

    
    $html = '<div class="btn-group">
             <button data-toggle="dropdown" class="btn dropdown-toggle">' . __('Language:', 'ComI18N') . ' <span class="ico-flags ' . $currLanguage->ico . '"></span>' . $currLanguage->title . ' <span class="caret"></span></button>';
    
    //$html = '<div class="bz-languages-container">' . __('Language:', 'ComI18N') . '</div>';
    if (count($languages) > 1) {
        $html .= '<ul id="bz_languages" class="dropdown-menu pull-right bz-languages-container">';
        foreach ($languages as $language) {
            $selected = '';
            $alias = $language->alias;
            if ($alias == $currLanguage->alias) {
                $selected = 'active';
            }
            if ($language->default_lang) {
                $alias = '';
            }
            $html .= '<li class="' . $selected . '" data-lang-id="' . $alias . '"><a href="#"><span class="ico-flags ' . $language->ico . '"></span> ' . $language->title . '</a></li>';
        }
        $html .= '</ul>';
    } else {
        $language = array_pop($languages);
        $html .= '<div class="bz-languages-container"><span class="ico-flags ' . $language->ico . '"></span>' . $language->title . '</div>';
    }
    $html .= '</div>';

    return $html;
}
