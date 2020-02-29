<?php

namespace JasperFW\FormBuilder\FormElement;

class Numberfield extends Input
{
    /**
     * @param string $name
     * @param array  $configuration
     * @param string $value
     */
    public function __construct(string $name, array $configuration = [], string $value = '')
    {
        parent::__construct($name, $configuration, 'number', $value);
    }
}