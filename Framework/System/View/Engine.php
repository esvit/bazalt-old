<?php

namespace Framework\System\View;

class Engine
{
    /**
     * Функція яка опрацьовує шаблон
     *
     * @param string $folder Папка, де лежить шаблон
     * @param string $file   Файл, який треба опрацювати
     * @param Scope  $view
     * @return mixed
     */
    public function fetch($folder, $file, Scope $view)
    {
        return file_get_contents($folder . PATH_SEP . $file);
    }
}