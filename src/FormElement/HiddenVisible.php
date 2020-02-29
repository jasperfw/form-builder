<?php

namespace JasperFW\FormBuilder\FormElement;

/**
 * Class HiddenVisible
 *
 * Use for hidden fields where the value should still be shown. Useful for a value you want to show the user but not
 * allow editing.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class HiddenVisible extends Input
{
    /**
     * @param string $name
     * @param array  $configuration
     * @param string $value
     */
    public function __construct(string $name, array $configuration = [], string $value = '')
    {
        parent::__construct($name, $configuration, 'hidden', $value);
    }

    /**
     * Returns the form element as an HTML tag
     */
    public function outputElement(): string
    {
        return $this->getValue() . parent::outputElement();
    }
}