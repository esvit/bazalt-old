<?php

namespace Framework\System\Multilingual;

use Framework\System\Multilingual\TranslateAdapter;

class ArrayTranslate extends TranslateAdapter
{
    protected $messages = [];

    public function messages($messages = null)
    {
        $lang = $this->scope->language();
        if ($messages != null) {
            $this->messages[$lang] = $messages;
            return $this;
        }
        return isset($this->messages[$lang]) ? $this->messages[$lang] : [];
    }

    public function translate($string, $pluralString = null, $count = null)
    {
        $lang = $this->scope->language();
        if (!isset($this->messages[$lang]) || !isset($this->messages[$lang][$string])) {
            return ($count == null || $count == 1) ? $string : $pluralString;
        }
        $message = $this->messages[$lang][$string];

        if (STAGE == TESTING_STAGE) {
            echo $string . ' => ';
        }
        if (isset($message['translation'])) {
            if (STAGE == TESTING_STAGE) {
                echo $message['translation'] . "\n";
            }
            return $message['translation'];
        }
        $index = $this->pluralIndex($count);
        if (STAGE == TESTING_STAGE) {
            echo 'pluralIndex(' . $count . ' => ' . $index . ')' . "\n";
        }
        $tr = $message['translations'];
        $str = ($count == 1) ? $string : $pluralString;
        if (isset($tr[$index])) {
            $str = $tr[$index];
            if (STAGE == TESTING_STAGE) {
                echo $str . "\n";
            }
        }
        return sprintf($str, $count);
    }
}