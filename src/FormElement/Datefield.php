<?php

namespace JasperFW\FormBuilder\FormElement;

/**
 * Class Datefield
 *
 * The date field is an HTML5 date field input type.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Datefield extends Input
{
    /**
     * @param string $name
     * @param array  $configuration
     * @param string $value
     */
    public function __construct(string $name, array $configuration = [], string $value = '')
    {
        parent::__construct($name, $configuration, 'date', $value);
    }
}