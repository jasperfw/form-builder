<?php

namespace JasperFW\FormBuilder\FormElement;

use Exception;
use JasperFW\FormBuilder\FormElement;

/**
 * Class Checkboxgroup
 *
 * The checkboxgroup is a selection list displayed as checkboxes.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Checkboxgroup extends Radiobutton
{
    protected $checked = [];

    /**
     *  Reset the checked options by unchecking all items.
     */
    public function resetChecks()
    {
        $this->checked = [];
    }

    /**
     * Sets the default or database value for the element. Overridden to convert arrays to strings.
     *
     * @param mixed $value The value of the form field.
     *
     * @return FormElement
     */
    public function setDefaultValue(mixed $value): FormElement
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        parent::setDefaultValue($value);
        return $this;
    }

    /**
     * Sets a user value to be validated. Overridden to convert arrays to strings.
     *
     * @param string $value
     *
     * @return FormElement
     */
    public function setUserValue(string $value): FormElement
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        parent::setUserValue($value);
        return $this;
    }

    /**
     * Return a list of elements that should be displayed checked.
     *
     * @return string[]
     */
    public function getChecked(): array
    {
        return $this->checked;
    }

    /**
     * Validates the user value according to the validator defined for this element.
     *
     * @throws Exception
     */
    public function validate(): void
    {
        if (null === $this->isValid) {
            if (null === $this->userValue && null !== $this->defaultValue) {
                // There is a preset value, and its not being changed
                $this->isValid = true;
            } elseif (null != $this->validator && false != $this->validator) {
                $validator = $this->validator;
                $valids = [];
                $allValid = true;
                $pieces = explode(',', $this->userValue);
                foreach ($pieces as $piece) {
                    $result = $validator::quickValidate($piece, $this->constraints);
                    if (null !== $result) {
                        $valids[] = $result;
                    } else {
                        $allValid = false;
                    }
                }
                $this->userValue = implode(',', $valids);
                $this->isValid = $allValid;
                if (!$allValid) {
                    $this->error = 'The provided value is not valid. Please enter a valid value.';
                }
            } else {
                // This field does not get validated
                $this->isValid = true;
            }
        }

        // Check if the input is different from the default value
        if ($this->isValid && null !== $this->userValue) {
            $this->isChanged = ($this->defaultValue != $this->userValue);
        }
    }

    /**
     * Returns the form element as an HTML tag
     *
     * @return string
     */
    public function outputElement(): string
    {
        $selected = explode(',', $this->getValue());
        $disabled = $this->disabled;
        $hidden = $this->hidden;

        $return = [];
        foreach ($this->options as $val => $txt) {
            if (in_array($val, $hidden)) {
                break;
            }
            $d = (in_array($val, $disabled)) ? ' disabled="disabled"' : '';
            $s = (in_array($val, $selected)) ? ' checked="checked"' : '';
            $return[] = '<input id="cb-' .
                str_replace(
                    ' ',
                    '-',
                    $val
                ) .
                '" type="checkbox" name="' .
                $this->attributes['name'] .
                '[]" value="' .
                $val .
                '"' .
                $d .
                $s .
                ' />' .
                $this->generateOptionLabel(
                    $val,
                    $txt
                );
        }
        return implode('', $return);
    }
}
