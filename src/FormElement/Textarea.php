<?php

namespace JasperFW\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement;

/**
 * Class Textarea
 *
 * A standard textarea form element
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Textarea extends FormElement
{
    protected string $value;

    /**
     * @param string $name
     * @param array  $configuration
     */
    public function __construct(string $name, array $configuration = [])
    {
        parent::__construct($name, $configuration);
    }

    /**
     * Returns the form element as an HTML tag
     */
    public function outputElement(): string
    {
        $atts = [];
        foreach ($this->attributes as $name => $value) {
            if ($name !== 'value') {
                $atts[] = $name . '="' . $value . '"';
            }
        }
        if (count($this->classes) > 0) {
            $atts[] = 'class="' . implode(' ', $this->classes) . '"';
        }
        $return = '<textarea ' . implode(' ', $atts) . '>' . "\n";
        $return .= $this->getValue();
        $return .= '</textarea>';
        return $return;
    }
}
