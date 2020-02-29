<?php

namespace JasperFW\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement;

/**
 * Class Input
 *
 * Form element that represents an input field.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Input extends FormElement
{
    /**
     * @param string $name
     * @param array  $configuration
     * @param string $type
     * @param string $value
     */
    public function __construct(string $name, array $configuration = [], string $type = '', string $value = '')
    {
        $this->attributes['value'] = $value;
        $this->defaultValue = $value;
        parent::__construct($name, $configuration);
        $this->attributes['type'] = $type;
    }

    /**
     * Make sure the user submitted value shows up when the form element is rendered.
     *
     * @param mixed $value
     *
     * @return FormElement
     */
    public function setUserValue(string $value): FormElement
    {
        parent::setUserValue($value);
        $this->attributes['value'] = $value;
        return $this;
    }
}