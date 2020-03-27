<?php

namespace JasperFW\FormBuilder\FormElement;

use JasperFW\Core\Callback\CallbackDefinition;
use JasperFW\FormBuilder\Form;
use JasperFW\FormBuilder\FormElement;

/**
 * Class Checkbox
 *
 * The Checkbox class is intended for use when a single checkbox is needed, for example to represent a bit field in a
 * database. If a group of checkboxes are needed, to represent multiple possible options for a single field, the
 * CheckboxGroup class should be used instead.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class Checkbox extends Input
{
    /**
     * @param string $name
     * @param array  $configuration
     * @param string $value
     */
    public function __construct(string $name, array $configuration = [], string $value = '')
    {
        parent::__construct($name, $configuration, 'checkbox', $value);
        if (isset($configuration['selected']) && $configuration['selected']) {
            $this->setChecked(true);
        }
    }

    public function setFormReference(Form &$form): FormElement
    {
        parent::setFormReference($form);
        $this->form->registerPopulateCallback(new CallbackDefinition($this, 'afterPopulate'));
        return $this;
    }

    /**
     * Set the checked state of the form element.
     *
     * @param bool $checked
     *
     * @return FormElement
     */
    public function setChecked(bool $checked = true): FormElement
    {
        if (true === $checked) {
            $this->attributes['checked'] = 'checked';
        } else {
            unset($this->attributes['checked']);
        }
        return $this;
    }

    /**
     * Overrides the default validation to make sure the value is 0 or 1, or true or false
     */
    public function validate(): void
    {
        if (null === $this->isValid) {
            if (null === $this->userValue && null !== $this->defaultValue) {
                // There is a preset value, and its not being changed
                $this->isValid = true;
            } elseif (true == $this->user_value || 1 == $this->userValue) {
                $this->userValue = 1;
                $this->isValid = true;
            } elseif (false == $this->userValue || 0 == $this->userValue || '' == $this->userValue) {
                $this->userValue = 0;
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
     * TODO: Redo this
     */
    public function outputElement(): string
    {
        if ('1' == $this->getValue()) {
            $this->setChecked(true);
        } else {
            $this->setChecked(false);
        }
        $attributes = [];
        foreach ($this->attributes as $name => $value) {
            $attributes[$name] = $name . '="' . $value . '"';
        }
        $attributes['value'] = 'value="1"';
        return '<input ' . implode(' ', $attributes) . ' />';
    }

    /**
     * Callback to run after the checkbox is populated. Sets the value to 0 if no user value was set
     */
    public function afterCallback(): void
    {
        if ($this->userValue != 1) {
            $this->userValue = 0;
        }
    }
    
    public function afterPopulate(): void
    {
        // TODO: This?
    }
}
