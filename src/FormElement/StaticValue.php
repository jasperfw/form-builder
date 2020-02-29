<?php

namespace JasperFW\FormBuilder\FormElement;

/**
 * Class StaticValue
 *
 * Use this for values you don't want to be visible or editable by the user. These values are not part of the form, and
 * won't be submitted. As such, they can't be altered with external tools like Fiddler. For more information about the
 * capabilities of this type, see StaticVisibleValue.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class StaticValue extends StaticVisibleValue
{
    /**
     * Shows the label as an html tag
     */
    public function outputLabel(): string
    {
        return '';
    }

    /**
     * Returns the form element as an HTML tag
     */
    public function outputElement(): string
    {
        return '';
    }

    /**
     * If the field is set to not be editable, this will output the value of the form element instead of a form field.
     */
    public function outputValue(): string
    {
        return '';
    }

    /**
     * Outputs the form element and label if one is defined.
     */
    public function __toString(): string
    {
        return '';
    }
}