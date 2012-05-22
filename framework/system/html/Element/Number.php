<?php
/**
 * Number.php
 *
 * @category   Html
 * @package    BAZALT
 * @subpackage System
 * @copyright  2011 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */

/**
 * Html_Element_Number
 *
 * @category   Html
 * @package    BAZALT
 * @subpackage System
 * @copyright  2011 Equalteam
 * @license    GPLv3
 * @version    $Revision: 133 $
 */
class Html_Element_Number extends Html_Element_Input
{
    const DEFAULT_CSS_CLASS = 'bz-form-input-number';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('readonly', 'boolean');   // Устанавливает, что поле не может изменяться пользователем.
        $this->validAttribute('maxlength', 'int');      // Максимальное количество символов разрешенных в тексте.
        $this->validAttribute('size', 'int');           // Ширина текстового поля.
        $this->validAttribute('value', 'mixed');                 // Значение элемента.
        $this->validAttribute('min', 'int');            // The expected lower bound for the element’s value.
        $this->validAttribute('max', 'int');            // The expected upper bound for the element’s value.

        $this->template('elements/number');

        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->type('number');
    }
    
    public function validate()
    {
        if (isset($this->attributes['min']) || isset($this->attributes['max'])) {
            if(isset($this->attributes['min']) && isset($this->attributes['max'])) {
                $res = $this->attributes['min'] <= $this->value() && $this->value() <= $this->attributes['max'];
                if(!$res) {
                    $this->addError('value', sprintf('"%s" is out of range "%s - %s"', $this->value(), $this->attributes['min'], $this->attributes['max']));
                }
                return $res;
            } else if(isset($this->attributes['min'])) {
                $res = $this->attributes['min'] <= $this->value();
                if(!$res) {
                    $this->addError('value', sprintf('"%s" is less than "%s"', $this->value(), $this->attributes['min']));
                }
                return $res;
            } else if(isset($this->attributes['max'])) {
                $res = $this->value() <= $this->attributes['max'];
                if(!$res) {
                    $this->addError('value', sprintf('"%s" is greater than "%s"', $this->value(), $this->attributes['max']));
                }
                return $res;
            }
        }
        $res = is_numeric($this->value());
        if(!$res) {
            $this->addError('value', sprintf('"%s" is not a number', $this->value()));
        }
        return $res;
    }
}
