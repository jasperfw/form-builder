<?php

namespace JasperFW\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement;
use JasperFW\Validator\Exception\BadDefinitionException;

/**
 * Class Select
 *
 * The select class is used to store data that is intended to be used to display a pulldown for a select field. Options
 * are stored in a keyed array where the key is the value attribute and the value is the text that would be shown to the
 * user.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Select extends FormElement
{
    protected $options = [];
    protected $hidden = [];
    protected $selected = '';
    protected $disabled = [];
    protected $addBlank = false;
    protected $blankText = '';

    /**
     * Create a new select with an optional list of options.
     *
     * @param string $name
     * @param array  $configuration
     * @param array  $options
     */
    public function __construct(string $name, array $configuration = [], array $options = [])
    {
        parent::__construct($name, $configuration);
        $this->setOptions($options);
        if (isset($configuration['addBlank'])) {
            $this->addBlankOption();
        }
        if (isset($configuration['blankText'])) {
            $this->blankText = $configuration['blankText'];
        }
        // If the values are entered, add the options
        if (isset($configuration['values']) && is_array($configuration['values'])) {
            $this->setOptions($configuration['values']);
        }
        if (isset($configuration['hiddenOptions']) && is_array($configuration['hiddenOptions'])) {
            foreach ($configuration['hiddenOptions'] as $name) {
                $this->hideValue($name);
            }
        }
        if (isset($configuration['disabledOptions']) && is_array($configuration['disabledOptions'])) {
            foreach ($configuration['disabledOptions'] as $name) {
                $this->disableValue($name);
            }
        }
    }

    /**
     * Add a single value to the end of the list.
     *
     * @param string $value The value of the option
     * @param string $text  The text to show the user
     *
     * @return FormElement
     */
    public function addOption(string $value, string $text): FormElement
    {
        $this->options[$value] = $text;
        return $this;
    }

    /**
     * Add a single value to the beginning of the list of elements.
     *
     * @param string $value
     * @param string $text
     *
     * @return FormElement
     */
    public function addOptionToBeginning(string $value, string $text): FormElement
    {
        $this->options = array_merge([$value => $text], $this->options);
        return $this;
    }

    /**
     * Values set to be hidden will not be included in the output
     *
     * @param string $value The value to hide
     *
     * @return Select
     */
    public function hideValue(string $value): Select
    {
        $this->hidden[] = $value;
        return $this;
    }

    /**
     * If a value is set to be hidden, unhide it.
     *
     * @param string $value The value to unhide
     *
     * @return Select
     */
    public function unhideValue(string $value): Select
    {
        //TODO: This
        return $this;
    }

    /**
     * Set the option as disabled.
     *
     * @param string $value
     *
     * @return Select
     */
    public function disableValue(string $value): Select
    {
        $this->disabled[] = $value;
        return $this;
    }

    /**
     * Specify an option to be preselected.
     *
     * @param string $value
     *
     * @return Select
     */
    public function setValue(string $value): Select
    {
        $this->selected = $value;
        return $this;
    }

    /**
     * Get the options for the pulldown as an array.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set an array of values to include as options in the select. This array should be a keyed array where the value
     * attribute is the key and the text to show to the user should be the value.
     *
     * @param string[] $options The options to display
     */
    public function setOptions(array $options): void
    {
        if (is_array($options)) {
            $this->options = $options;
        }
    }

    /**
     * Force a blank option to be added at the top of the pulldown. Optionally pass true to cause that option to not be
     * selectable.
     *
     * @param bool   $disabled
     * @param string $blankText The text to be displayed in the dropdown for the blank option
     *
     * @return Select
     */
    public function addBlankOption(bool $disabled = false, string $blankText = ''): Select
    {
        $this->addBlank = true;
        if ($disabled) {
            $this->disabled[] = '';
        }
        $this->blankText = $blankText;
        return $this;
    }

    /**
     * If the blank options was added, this function allows it to be removed.
     *
     * @return Select
     */
    public function removeBlankOption(): Select
    {
        $this->addBlank = false;
        return $this;
    }

    /**
     * Overrides the normal validation to instead make sure the value passed is a value that has been set, and the value
     * is not disabled.
     */
    public function validate(): void
    {
        if (null === $this->isValid) {
            if (null === $this->userValue && null !== $this->defaultValue) {
                // There is a preset value, and its not being changed
                $this->isValid = true;
            } elseif (in_array($this->userValue, array_keys($this->options)) && !in_array(
                    $this->userValue,
                    $this->disabled
                )
            ) {
                $this->isValid = true;
            } elseif ('' == $this->userValue && $this->addBlank && !in_array('', $this->disabled)) {
                $this->isValid = true;
            } elseif (false == $this->validator) {
                // This field does not get validated
                $this->isValid = true;
            } else {
                $this->isValid = false;
                $this->error = 'The provided value is not valid. Please enter a valid value.';
            }
        }

        // Check if the input is different from the default value
        if ($this->isValid && null !== $this->userValue) {
            $this->isChanged = ($this->defaultValue != $this->userValue);
        }
    }

    /**
     * Returns the form element as an HTML tag
     *
     * @return string
     * @throws BadDefinitionException
     */
    public function outputElement(): string
    {
        $this->selected = $this->getValue();
        $atts = [];
        foreach ($this->attributes as $name => $value) {
            if ($name !== 'value') {
                $atts[] = $name . '="' . $value . '"';
            }
        }
        if (count($this->classes) > 0) {
            $atts[] = 'class="' . implode(' ', $this->classes) . '"';
        }
        $return = '<select ' . implode(' ', $atts) . '>' . "\n";
        if ($this->addBlank) {
            $disabled = (in_array('', $this->disabled)) ? ' disabled="disabled"' : '';
            $selected = ($this->selected === '') ? ' selected="selected"' : '';
            $return .= '<option value=""' . $disabled . $selected . '>' . $this->blankText . '</option>' . "\n";
        }
        foreach ($this->options as $val => $txt) {
            if (in_array($val, $this->hidden)) {
                continue;
            }
            $disabled = (in_array($val, $this->disabled)) ? ' disabled="disabled"' : '';
            $selected = ($this->selected == $val) ? ' selected="selected"' : '';
            $return .= '<option value="' . $val . '"' . $disabled . $selected . '>' . $txt . '</option>' . "\n";
        }
        $return .= '</select>';
        return $return;
    }

    /**
     * If the field is set to not be editable, this will output the value of the form element instead of a form field.
     */
    public function outputValue(): string
    {
        if (isset($this->options[$this->getValue()])) {
            return '<span class="uneditable-field">' . $this->options[$this->getValue()] . '</span>';
        } else {
            return '<span class="uneditable-field">' . $this->getValue() . '</span>';
        }
    }
}
