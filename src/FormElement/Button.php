<?php

namespace JasperFW\FormBuilder\FormElement;

/**
 * Class Button
 *
 * The button is a standard button input type.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Button extends Input
{
    /**
     * @param string $name
     * @param array  $configuration
     * @param string $value
     */
    public function __construct(string $name, array $configuration = [], string $value = '')
    {
        parent::__construct($name, $configuration, 'button', $value);
        if (isset($configuration['onClick'])) {
            $this->attributes['onClick'] = $configuration['onClick'];
        }
    }

    /**
     * If the field is set to not be editable, this will output the value of the form element instead of a form field.
     */
    public function outputValue(): string
    {
        return '';
    }
}
