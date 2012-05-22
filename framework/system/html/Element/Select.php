<?php

/**
 *
 */
class Html_Element_Select extends Html_FormElement
{
    const DEFAULT_CSS_CLASS = 'bz-form-select';

    /**
     * Кількість опції у списку, для того щоб давати послідовну нумерацію, 
     * якщо не встановлене значення користувачем
     */
    protected $count = 0;

    /**
     * Групи опцій, 0 група - це основна група списку, додається автоматично
     */
    protected $groups = array();

    public function initAttributes()
    {
        parent::initAttributes();

        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->template('elements/select');

        $this->validAttribute('disabled', 'boolean'); // Блокирует доступ и изменение элемента. 
        $this->validAttribute('multiple', 'boolean'); // Позволяет одновременно выбирать сразу несколько элементов списка.
        $this->validAttribute('name');                // Имя элемента для отправки на сервер или обращения через скрипты.
        $this->validAttribute('size',     'int');     // Количество отображаемых строк списка.

        $this->groups []= new Html_Element_SelectGroup($this);
    }

    /**
     * Повертає індекс опції і збільшує лічильник
     */
    public function getNextOptionIndex()
    {
        return ++$this->count;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Додає групу для опцій
     *
     * @param string $title Заголовок групи
     *
     * @return Html_Element_SelectGroup об'єкт групи
     */
    public function addGroup($title)
    {
        $group = new Html_Element_SelectGroup($this, $title);
        $this->groups []= $group;
        return $group;
    }

    /**
     * Додає опцію до списку
     *
     * @param string $title Заголовок
     * @param string $value Значення
     *
     * @return void
     */
    public function addOption($title, $value = null)
    {
        $this->groups[0]->addOption($title, $value);
        return $this;
    }

    public function value($value = null)
    {
        if ($value !== null) {
            if ((!$this->multiple() && is_array($value)) || ($this->multiple() && !is_array($value))) {
                throw new Exception('Invalid select value');
            }
            return parent::value($value);
        }
        return parent::value();
    }

    public function name($name = null)
    {
        if ($name != null) {
            return parent::name($name);
        }
        $name = parent::name();
        if ($this->multiple()) {
            $name .= '[]';
        }
        return $name;
    }

    /**
     * Перевірка значень, щоб користувач не надіслав значення,
     * яких нема у списку, наприклад, через FireBug
     */
    public function validate()
    {
        $value = $this->value();
        if (!$this->multiple()) {
            $value = array($value);
        }
        $values = array();
        foreach ($this->groups as $group) {
            $values = array_merge($values, $group->getValues());
        }
        $diff = array_diff($value, $values);
        if (count($value) > 0 && count($diff) > 0) {
            $this->addError('value', 'Posted value not found in values');
            return false;
        }
        return parent::validate();
    }
}
