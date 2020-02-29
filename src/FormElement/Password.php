<?php

namespace JasperFW\FormBuilder\FormElement;

/**
 * Class Password
 *
 * Standard password input type
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Password extends Input
{
    /**
     * @param string $name
     * @param array  $configuration
     * @param string $value
     */
    public function __construct(string $name, array $configuration = [], string $value = '')
    {
        parent::__construct($name, $configuration, 'password', $value);
    }
}