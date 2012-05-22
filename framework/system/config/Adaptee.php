<?php

class Config_Adaptee extends DataType_Adaptee implements Config_IConfigurable
{
    /**
     * Завантажує конфіг
     *
     * @param mixed $elem Конфіг
     *
     * @return void
     */
    public function configure($config)
    {
        if (empty($config)) {
            throw new Exception('Unknown adapter');
        }
        $this->adapterClass = $config->value;

        if (empty($this->adapterClass)) {
            throw new Exception('Unknown adapter');
        }
        $arr = $config->attributes;

        if (isset($arr['namespace'])) {
            $this->adapterNamespace = $arr['namespace'];
            unset($arr['namespace']);
        }

        $this->adapterOptions = $arr;
        return $this;
    }
}