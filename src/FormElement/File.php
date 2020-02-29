<?php

namespace JasperFW\FormBuilder\FormElement;

/**
 * Class File
 *
 * File input type.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class File extends Input
{
    /**
     * @param string $name
     * @param array  $configuration
     * @param string $value
     */
    public function __construct(string $name, array $configuration = [], string $value = '')
    {
        parent::__construct($name, $configuration, 'file', $value);
    }
}