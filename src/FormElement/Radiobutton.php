<?php

namespace JasperFW\FormBuilder\FormElement;

/**
 * Class Radiobutton
 *
 * A radiobuton is a select list represented instead as a set of radio buttons that the user can select from.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Radiobutton extends Select
{
    protected bool $addBlank = false;

    /**
     * Create a new select with an optional list of options.
     *
     * @param string $name
     * @param array  $configuration
     * @param array  $options
     */
    public function __construct(string $name, array $configuration = [], array $options = [])
    {
        if (isset($configuration['addBlank'])) {
            unset($configuration['addBlank']);
        }
        parent::__construct($name, $configuration, $options);
    }

    /**
     * Force a blank option to be added at the top of the pulldown. Optionally pass true to cause that option to not be
     * selectable.
     *
     * TODO: Store the blank text as is done in Select
     *
     * @param bool   $disabled
     * @param string $blankText
     *
     * @return Select
     */
    public function addBlankOption(bool $disabled = false, string $blankText = ''): Select
    {
        $this->addBlank = false;
        return $this;
    }

    /**
     * Returns the form element as an HTML tag
     *
     * TODO: Output the blank text
     *
     * @return string
     */
    public function outputElement(): string
    {
        $this->selected = $this->getValue();

        $return = [];
        foreach ($this->options as $val => $txt) {
            if (in_array($val, $this->hidden)) {
                break;
            }
            $disabled = (in_array($val, $this->disabled)) ? ' disabled="disabled"' : '';
            $selected = ($this->selected == $val) ? ' checked' : '';
            $return[] = '<input type="radio" id="cb-' .
                str_replace(
                    ' ',
                    '-',
                    $val
                ) .
                '" name="' .
                $this->attributes['name'] .
                '" value="' .
                $val .
                '"' .
                $disabled .
                $selected .
                ' /> ' .
                $this->generateOptionLabel(
                    $val,
                    $txt
                );
        }
        return implode('<br>' . "\n", $return);
    }

    public function outputLabel(): string
    {
        return '';
    }

    /**
     * Creates the label for an option
     *
     * @param string $val The value of the option
     * @param string $txt The text of the option
     *
     * @return string The label
     */
    protected function generateOptionLabel(string $val, string $txt): string
    {
        $labelClass = count($this->labelClasses) > 0 ? ' class="' . implode(' ', $this->labelClasses) . '"' : '';
        return '<label ' . $labelClass . ' for="cb-' . str_replace(' ', '-', $val) . '">' . $txt . '</label>';
    }
}
