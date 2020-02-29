<?php

namespace JasperFW\FormBuilder\FormElement;

/**
 * Class Textfield
 *
 * Standard input for text.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Textfield extends Input
{
    /**
     * @param string $name
     * @param array  $configuration
     * @param string $value
     */
    public function __construct(string $name, array $configuration = [], string $value = '')
    {
        parent::__construct($name, $configuration, 'text', $value);
    }
}