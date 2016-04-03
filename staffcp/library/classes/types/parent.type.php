<?php

class ParentType extends Type {

    public function getFormValue() {
        $db = Register::get('db');

        $szNameField = $this->fieldInfo['cross_name'];
        $szIndex = $this->fieldInfo['cross_index'];
        $szPid = $this->fieldInfo['cross_pid'];
        $szTable = $this->fieldInfo['cross_table'];

        if (!empty($this->fieldInfo['cross_cond']))
            $szCond = ' WHERE ' . $this->fieldInfo['cross_cond'];

        elseif (!empty($this->fieldInfo['cross_group']))
            $szCond = ' WHERE `' . $this->fieldInfo['cross_parent'] . '`="' . $data->nID . '"';
        else
            $szCond = '';
        if (!empty($this->fieldInfo['ordered'])) {
            $szCond .= ' ORDER BY ' . $this->fieldInfo['ordered'];
        }

        $aResult = array();
        $aData = $db->query('SELECT ' . '`' . $szNameField . '`,`' . $szIndex . '`, `' . $szPid . '` FROM `' . $szTable . '`' . $szCond);

        $aParse = array();
        $result = '<select name="form[' . $this->getFieldName() . ']">';
        if (!empty($this->fieldInfo['first'])) {
            foreach ($this->fieldInfo['first'] as $key => $value) {
                if ($value == $this->value || (!is_null($this->filter) && $this->filter['field'] == $this->getFieldName()
                        && $this->filter['value'] == $value))
                    $selected = 'selected';
                else
                    $selected = '';
                $result .= '<option value="' . $key . '" ' . $selected . '>' . htmlspecialchars($value) . '</option>';
            }
        }

        $result .= $this->genTree($aData, 0, $szIndex, $szNameField, $szPid);

        $result .= "</select>";

        return $result;
    }

    public function getViewValue() {
        $current = $this->getValue();
        if (empty($current))
            return null;
        $db = Register::get('db');
        $szTable = $this->fieldInfo['cross_table'];
        $szIndex = $this->fieldInfo['cross_index'];
        $szNameField = $this->fieldInfo['cross_name'];

        $aResult = $db->query('SELECT * FROM `' . $szTable . '` WHERE `' . $szIndex . '`="' . $this->value . '" LIMIT 0,1');
        $szDefault = isset($this->fieldInfo['default']) ? $this->fieldInfo['default'] : '&nbsp;';
        $szResult = (!empty($aResult[0][$szNameField]) ? $aResult[0][$szNameField] : $szDefault);
        return $szResult;
    }

    protected function genTree($data, $pid, $szIndex, $szNameField, $szPid) {
        static $count;
        $count = (isset($count)) ? ++$count : 0;

        $results = null;
        foreach ($data as $key => $item) {
            if ($item[$szIndex] == $this->value || 
	            (!is_null($this->filter) && 
	            $this->filter['field'] == $this->getFieldName() && 
	            $this->filter['value'] == $item[$szIndex])
            )
                $selected = 'selected';
            else
                $selected = '';

            $l = null;
            for ($i = 0; $i < $count; $i++) {
                $l .= '&nbsp;&nbsp;';
            }

            if ($item[$szPid] == $pid) {
                if (0 == $pid) {
                    $results .= "<optgroup label='{$item[$szNameField]}'>";
                }

                $results .= '<option value="' . $item[$szIndex] . '" ' . $selected . ' >' . $l . htmlspecialchars($item[$szNameField]) . '</option>';

                $tmp = $this->genTree($data, $item[$szIndex], $szIndex, $szNameField, $szPid);

                if (count($tmp)) {
                    $results .= $tmp;
                }

                if (0 == $pid) {
                    $results .= "</optgroup>";
                }
            }
        }
        $count--;
        return $results;
    }

}