<?php

namespace JasperFW\FormBuilder\FormElement;

/**
 * Class Submit
 *
 * The submit button is a subtype of an input button
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Submit extends Input
{
    /**
     * @param string $name
     * @param array  $configuration
     * @param string $value
     */
    public function __construct(string $name, array $configuration = [], string $value = '')
    {
        parent::__construct($name, $configuration, 'submit', $value);
    }

    /**
     * The value of the submit button is immutable. Always return the default value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->defaultValue;
    }
}