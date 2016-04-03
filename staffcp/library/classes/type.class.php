<?php
/**
 * Class Type present base interface and base implenets of type functionality.
 * 
 */
abstract class Type {

    const NOT_SET = '___not_set_this_field_value__';

    public $value;
    public $fieldName;
    public $fieldInfo;
    public $indexValue;
    public $table;

    public $hasLayout = true;

    /**
     * Creates a type
     * @param string $fieldName type form field name
     * @param mixed $fieldInfo information about field (should contains at least
     * type of field)
     * @param mixed $value field value if set
     */
    public function __construct($fieldName, $fieldInfo, $value = null)
    {
        $this->fieldName = $fieldName;
        $this->setValue($value);
        if (is_array($fieldInfo))
        {
            $this->fieldInfo = $fieldInfo;
        } else {
            $this->fieldInfo = array('type'	=>	$fieldInfo);
        }
    }

    // @todo need to add checkers to all fields
    public function check($value,$fieldInfo = array())
    {
        return true;
    }

    /**
     * Get HTML-code for appearing of current type<br>
     * Used by generator while list of items rendering
     * @return string
     */
    public function getViewValue()
    {
        return $this->getValue();
    }

    /**
     * Get HTML-code for form appearing of current type<br>
     * Used by generator while edit or add form rendering
     * @return string
     */
    abstract public function getFormValue();

    /**
     * Get current type value
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set current type value
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get current type field name<br>
     * <i>But you should know, that when type field if form will be have name
     * form[<fieldname>]</i>
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Prepare type form value for saving in DB<br>
     * Used by generator in save actions
     * @param mixed $value form value
     * @return mixed
     */
    public function getSaveValue($value)
    {
        return $value;
    }
}