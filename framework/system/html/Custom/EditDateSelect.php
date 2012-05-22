<?php

class Html_Element_EditDateSelect extends Html_ContainerElement
{
    public function value($value = null)
    {
        if ($value != null && array_key_exists('month', $value)) {
            if ($value['month'] != '-' && $value['day'] != '-' && $value['year'] != '-') { 
                $time = mktime(1, 1, 1, intval($value['month']), intval($value['day']), intval($value['year']));

                $this->value = $time;
            } else {
                $this->value = null;
            }
            return $this;
        }
        if (!is_numeric($this->value)) {
            return null;
        }
        return date('Y-m-d H:i:s', $this->value);
    }

    protected function getDays()
    {
        $str = '<option value="-">-</option>';

        for ($i = 1; $i <= 31; $i++) {
            $selected = '';
            if ($this->value != null && date('d', $this->value) == $i) {
                $selected = ' selected="selected"';
            }
            $str .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
        }
        return $str;
    }


    protected function getYears()
    {
        $str = '<option value="-">-</option>';
        $year = intval(date('Y'));
        $startDate = $year - 1;
        $endDate = $year;

        for ($i = $endDate; $i > $startDate; --$i) {
            $selected = '';
            if ($this->value != null && date('Y', $this->value) == $i) {
                $selected = ' selected="selected"';
            }
            $str .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
        }
        return $str;
    }

    protected function getMonthes()
    {
        $monthes = array (
            __('January', ComUsers::getName()),
            __('February', ComUsers::getName()),
            __('March', ComUsers::getName()),
            __('April', ComUsers::getName()),
            __('May', ComUsers::getName()),
            __('June', ComUsers::getName()),
            __('July', ComUsers::getName()),
            __('August', ComUsers::getName()),
            __('September', ComUsers::getName()),
            __('October', ComUsers::getName()),
            __('November', ComUsers::getName()),
            __('December', ComUsers::getName())
        );
        $str = '<option value="-">-</option>';

        foreach ($monthes as $i => $month) {
            $selected = '';
            if ($this->value != null && date('n', $this->value) == $i + 1) {
                $selected = ' selected="selected"';
            }
            $str .= '<option value="' . ($i + 1) . '" ' . $selected . '>' . $month . '</option>';
        }
        return $str;
    }

    public function validate()
    {
        $result = true;
        foreach ($this->validators as $val) {
            $res = $val->validate($this, $this->form);
            $result = $result & $res;
        }
        return $result;
    }

    public function toString()
    {
        BazaltCms::getComponent('ComUsers');
        $cls = 'bz-form-row bz-form-date-row';
        if (count($this->errors) > 0) {
            $cls .= ' bz-form-row-has-error';
        }
        $str = '<div class="' . $cls . '">';

        $str .= $this->renderLabel();

        $str .= '<div class="bz-form-date-part"><label for="dt-2">' . __('Day', ComUsers::getName()) . '</label><div class="bz-form-date-select">';
        $str .= '<select autocomplete="off" name="' . $this->name() . '[day]">';
        $str .= $this->getDays();
        $str .= '</select></div></div>';

        $str .= '<div class="bz-form-date-part"><label for="dt-1">' . __('Month', ComUsers::getName()) . '</label><div class="bz-form-date-select">';
        $str .= '<select autocomplete="off" name="' . $this->name() . '[month]">';
        $str .= $this->getMonthes();
        $str .= '</select></div></div>';

        $str .= '<div class="bz-form-date-part"><label for="dt-0">' . __('Year', ComUsers::getName()) . '</label><div class="bz-form-date-select">';
        $str .= '<select autocomplete="off" name="' . $this->name() . '[year]">';
        $str .= $this->getYears();
        $str .= '</select></div></div>';

        $str .= '<div class="spacer"></div>';
        $str .= '</div>';
        return $str;
    }
}