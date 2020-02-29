<?php

namespace JasperFW\FormBuilder\FormElement;

/**
 * Class Emailfield
 *
 * HTML5 email input type
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Emailfield extends Input
{
    /**
     * @param string $name
     * @param array  $configuration
     * @param string $value
     */
    public function __construct(string $name, array $configuration = [], string $value = '')
    {
        parent::__construct($name, $configuration, 'email', $value);
    }
}