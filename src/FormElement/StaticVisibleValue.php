<?php

namespace JasperFW\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement;

/**
 * Class StaticVisibleValue
 *
 * Use this for values you want to display, without the value being editable or changeable. Because the fields are not
 * editable, they can't be
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class StaticVisibleValue extends Input
{
    /**
     * Returns the form element as plain text that can not be edited.
     */
    public function outputElement(): string
    {
        return $this->getValue();
    }

    /**
     * @param string $value
     *
     * @return FormElement
     */
    public function setUserValue(string $value): FormElement
    {
        // Does nothing
        return $this;
    }

    /**
     * Since this value can't be changed, it is always valid.
     */
    public function validate(): void
    {
        $this->isValid = true;
    }
}