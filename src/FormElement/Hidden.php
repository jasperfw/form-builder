<?php

namespace JasperFW\FormBuilder\FormElement;

/**
 * Class Hidden
 *
 * Use for hidden fields.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Hidden extends Input
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
     * Shows the label as an html tag
     */
    public function outputLabel(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function outputValue(): string
    {
        return '';
    }

    /**
     * Override parent because this does not output classes or any help or error text.
     *
     * @return string
     */
    public function outputElement(): string
    {
        $attributes = [];
        foreach ($this->attributes as $name => $value) {
            if ('value' === $name) {
                $value = $this->getValue();
            }
            $attributes[] = $name . '="' . $value . '"';
        }
        return '<input ' . implode(' ', $attributes) . ' />';
    }

    public function outputError(): string
    {
        return '';
    }

    public function outputHelpText(): string
    {
        return '';
    }

    public function __toString(): string
    {
        return $this->outputElement();
    }
}