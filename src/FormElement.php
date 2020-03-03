<?php

namespace JasperFW\FormBuilder;

use JasperFW\Validator\Filter\StripTags;
use JasperFW\Validator\Filter\Trim;
use JasperFW\Validator\Validator\Validator;

/**
 * Class FormElement
 *
 * FormElement objects represent various input fields that might be found in an HTML form, along with settings for the
 * validation of the form input.
 *
 * @package JasperFW\FormBuilder
 * @property mixed  userValue  The value for the field as submitted by the user.
 * @property string name       The name of the element in the form
 */
abstract class FormElement
{
    protected $attributes = [
        'id' => '',
    ];
    /** @var string The label text for this field */
    protected $label = null;
    /** @var string[] Array of classes to be applied to the label */
    protected $labelClasses = [];
    /** @var Validator $validator */
    protected $validator = null;
    /** @var array Array of filters to pass to the validator */
    protected $filters = [];
    /** @var array Array of constraints to pass the validator */
    protected $constraints = [];
    /** @var mixed The default value or the value from the database */
    protected $defaultValue = null;
    /** @var mixed The user submited value */
    protected $userValue = null;
    /** @var string|null|bool The name of the value in the database */
    protected $dbName = null;
    /** @var string|null The name of the value to request from the database (good for when the value is being cast) */
    protected $selectName = null;
    /** @var null|bool True or false based on validity, or null if the value has not been checked yet. */
    protected $isValid = null;
    /** @var bool True if this element is a property of an object, and is contained in the objects db row */
    protected $isProperty = true;
    /** @var null|string The error message to be displayed */
    protected $error = null;
    /** @var array Array of classes for the error text span */
    protected $errorClasses = [];
    /** @var bool True if this field must have a value that is not empty */
    protected $required = true;
    /** @var string The default error message to display */
    protected $defaultError = 'The entered value is not valid.';
    /** @var Form|null Reference to the form this element is contained in */
    protected $form = null;
    /** @var bool True if the form field has been changed (a valid value different from the default was set) */
    protected $isChanged = false;
    /** @var bool If true, if the valid value of this field is empty, null is returned instead of '' */
    protected $nullOnEmpty = false;
    /** @var string The type of value, string, int, float. */
    protected $type = 'string';
    /** @var bool If true, displays normally. If false, outputs as plain text instead of a form element */
    protected $editable = true;
    /** @var array The list of classes that will be put into the list */
    protected $classes = [];
    /** @var string The help or assistive text to display for the element */
    protected $helpText;
    /** @var string[] Array of classes for the help text */
    protected $helpTextClasses = [];
    /** @var string The template that sets how the parts of this form element will be ordered. */
    protected $template;

    /**
     * The protected constructor will prevent this parent class from being instantiated.
     *
     * @param string $name          The name of this field
     * @param array  $configuration Configuration settings for the form element
     */
    public function __construct(string $name, array $configuration = [])
    {
        $this->attributes['name'] = $name;
        $this->attributes['id'] = $name;
        if (isset($configuration['id'])) {
            $this->attributes['id'] = $configuration['id'];
        }
        // Set up the validator
        if (isset($configuration['validator']) &&
            null != $configuration['validator'] &&
            false != $configuration['validator']) {
            $field_filters = (isset($configuration['filters'])) ? $configuration['filters'] : [];
            $field_constraints = (isset($configuration['constraints'])) ? $configuration['constraints'] : [];
            $this->setValidator($configuration['validator'], $field_filters, $field_constraints);
        }
        // Check if the field is optional or required
        if (isset($configuration['optional'])) {
            $this->setRequired(!$configuration['optional']);
        }
        if (isset($configuration['required'])) {
            $this->setRequired($configuration['required']);
        }
        // Process other configuration directives
        if (isset($configuration['class'])) {
            if (is_array($configuration['class'])) {
                foreach ($configuration['class'] as $class) {
                    $this->addClass($class);
                }
            } else {
                $this->addClass($configuration['class']);
            }
        }
        if (isset($configuration['label'])) {
            $this->setLabel($configuration['label']);
        }
        if (isset($configuration['labelClass'])) {
            if (is_array($configuration['labelClass'])) {
                foreach ($configuration['labelClass'] as $class) {
                    $this->addLabelClass($class);
                }
            } else {
                $this->addLabelClass($configuration['labelClass']);
            }
        }
        if (isset($configuration['default'])) {
            $this->setDefaultValue($configuration['default']);
            $this->setAttribute('value', $configuration['default']);
        }
        if (isset($configuration['dbname'])) {
            $this->setDBName($configuration['dbname']);
        }
        if (isset($configuration['selectName'])) {
            $this->setSelectName($configuration['selectName']);
        }
        if (isset($configuration['nullOnEmpty'])) {
            $this->setNullOnEmpty($configuration['nullOnEmpty']);
        }
        if (isset($configuration['property'])) {
            $this->setIsProperty($configuration['property']);
        }
        if (isset($configuration['type'])) {
            $this->setType($configuration['type']);
        }
        if (isset($configuration['helpText'])) {
            $this->setHelpText($configuration['helpText']);
        }
        if (isset($configuration['helpClass'])) {
            if (is_array($configuration['helpClass'])) {
                foreach ($configuration['helpClass'] as $class) {
                    $this->addHelpTextClass($class);
                }
            } else {
                $this->addHelpTextClass($configuration['helpClass']);
            }
        }
        if (isset($configuration['errorClass'])) {
            if (is_array($configuration['errorClass'])) {
                foreach ($configuration['errorClass'] as $class) {
                    $this->addErrorClass($class);
                }
            } else {
                $this->addErrorClass($configuration['errorClass']);
            }
        }
        if (isset($configuration['template'])) {
            $this->setTemplate($configuration['template']);
        }
        if (null != $this->validator) {
            $this->filters[] = Trim::class;
            if (!isset($configuration['stripTags']) || $configuration['stripTags'] === false) {
                $this->filters[] = StripTags::class;
            }
        }
    }

    /**
     * Get the value of the attribute requested.
     *
     * @param string $name
     *
     * @return null|string
     */
    public function __get(string $name): ?string
    {
        if ($name === 'validator') {
            return $this->validator;
        }
        if ($name === 'userValue') {
            return $this->userValue;
        }
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return null;
    }

    /**
     * Set an attribute for the form's html tag. Replaces the existing value.
     *
     * @param string $name  The name of the attribute
     * @param string $value The new value of the attribute
     *
     * @return self
     */
    public function setAttribute(string $name, string $value): FormElement
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Sets the id to the specified value
     *
     * @param string $id
     *
     * @return self
     */
    public function setId(string $id): FormElement
    {
        $this->attributes['id'] = $id;
        return $this;
    }

    /**
     * Add the specified class to the class attribute for the form field.
     *
     * @param string $value A class to add
     *
     * @return self
     */
    public function addClass(string $value): FormElement
    {
        $newClass = trim($value);
        if (!in_array($newClass, $this->classes)) {
            $this->classes[] = $newClass;
        }
        return $this;
    }

    /**
     * Remove a specified class.
     *
     * @param string $value The class to remove
     *
     * @return $this
     */
    public function removeClass(string $value): FormElement
    {
        $value = trim($value);
        if (($key = array_search($value, $this->classes)) !== false) {
            unset($this->classes[$key]);
        }
        return $this;
    }

    /**
     * Add a class to the label for this form element
     *
     * @param string $newClass The name of the class to add
     *
     * @return $this
     */
    public function addLabelClass(string $newClass): FormElement
    {
        $this->labelClasses[] = $newClass;
        return $this;
    }

    /**
     * Remove the specified class from the label
     *
     * @param string $value The name of the class to remove
     *
     * @return $this
     */
    public function removeLabelClass(string $value): FormElement
    {
        if (($key = array_search($value, $this->labelClasses)) !== false) {
            unset($this->labelClasses[$key]);
        }
        return $this;
    }

    /**
     * Help or assistive text to be displayed near the form element
     *
     * @param string $helpText The text to display
     *
     * @return $this
     */
    public function setHelpText(string $helpText): FormElement
    {
        $this->helpText = $helpText;
        return $this;
    }

    /**
     * Add a class for the span that contains help text for this element
     *
     * @param string $newClass
     *
     * @return $this
     */
    public function addHelpTextClass(string $newClass): FormElement
    {
        $this->helpTextClasses[] = $newClass;
        return $this;
    }

    /**
     * Remove a class from the span that contains help text for this element
     *
     * @param string $value
     *
     * @return $this
     */
    public function removeHelpTextClass(string $value): FormElement
    {
        if (($key = array_search($value, $this->helpTextClasses)) !== false) {
            unset($this->helpTextClasses[$key]);
        }
        return $this;
    }

    /**
     * Add a class to the span containing the error message output
     *
     * @param string $value
     *
     * @return $this
     */
    public function addErrorClass(string $value): FormElement
    {
        $this->errorClasses[] = $value;
        return $this;
    }

    /**
     * Remove a class from the span containing error message output
     *
     * @param string $value
     *
     * @return $this
     */
    public function removeErrorClass(string $value): FormElement
    {
        if (($key = array_search($value, $this->errorClasses)) !== false) {
            unset($this->errorClasses[$key]);
        }
        return $this;
    }

    /**
     * Sets a validator for the field
     *
     * @param string $validatorClassName The name of the validation class
     * @param array  $filters            Optional filters that will be set on the validator
     * @param array  $constraints        Optional constraints
     *
     * @return self
     */
    public function setValidator(
        string $validatorClassName,
        array $filters = [],
        array $constraints = []
    ): FormElement {
        $this->validator = $validatorClassName;
        $this->filters = $filters;
        $this->constraints = $constraints;
        return $this;
    }

    /**
     * Set if the field is required.
     *
     * @param bool $required True if this field is required.
     *
     * @return self
     */
    public function setRequired(bool $required): FormElement
    {
        $required = (bool)$required;
        $this->required = $required;
        return $this;
    }

    /**
     * Set if null should be returned instead of an empty string when the value of the field is empty, and that empty
     * string is a valid value.
     *
     * @param bool $value
     *
     * @return self
     */
    public function setNullOnEmpty(bool $value): FormElement
    {
        $this->nullOnEmpty = $value;
        return $this;
    }

    /**
     * @return string The type of value
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the type of value this field should contain. Mostly used for database insertion of form field contents, this
     * will convert the value to a specified type.
     *
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type): FormElement
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Check if the element has been set as a property of the object or not.
     *
     * @return bool
     */
    public function isProperty(): bool
    {
        return $this->isProperty;
    }

    /**
     * For forms that represent objects, set to false if this form element is not a property of the object. This will
     * cause the element to be ommitted from sql queries.
     *
     * @param bool $value
     *
     * @return self
     */
    public function setIsProperty(bool $value): FormElement
    {
        $this->isProperty = $value;
        return $this;
    }

    /**
     * Returns the dbName. If no dbName is set, returns the name of the field.
     *
     * @return string|null
     */
    public function getDBName(): ?string
    {
        if (null !== $this->dbName) {
            return $this->dbName;
        }
        return $this->attributes['name'];
    }

    /**
     * The dbname is the name of the column in the database. This should be set to match the name as it will appear
     * in the database query. To unset the dbname, simply set the value to null. If this field should not be submitted
     * to the database, set the value to false.
     *
     * @param string|null|bool $name
     *
     * @return self
     */
    public function setDBName($name): FormElement
    {
        $this->dbName = $name;
        return $this;
    }

    /**
     * Returns the db select name of the field. If none is set, returns the dbName of the field, if that is not set,
     * returns the base name of the field.
     *
     * @return null|string
     */
    public function getSelectName(): ?string
    {
        if (null !== $this->selectName) {
            return $this->selectName . ' as ' . $this->getDBName();
        }
        if (null !== $this->dbName) {
            return $this->dbName;
        }
        return $this->attributes['name'];
    }

    /**
     * Set the name for the field that should be used when selecting this value from the database. This is useful when
     * some form of conversion has to be done on the value, such as casting a clob to a varchar.
     *
     * @param string $name
     *
     * @return self
     */
    public function setSelectName(string $name): FormElement
    {
        $this->selectName = $name;
        return $this;
    }

    /**
     * Get the label associated with the form field. Removes and trailing colon.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return trim(str_replace(':', '', $this->label));
    }

    /**
     * Creates a label tag that will be displayed before the form element.
     *
     * @param string $label
     *
     * @return self
     */
    public function setLabel(string $label): FormElement
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Gets the value of the field, either the user set value if valid and set, or the object value.
     *
     * @return mixed
     */
    public function getValue()
    {
        if (null == $this->isValid && null != $this->userValue) {
            $this->validate();
        }
        if (null !== $this->userValue && $this->isValid) {
            if ('' === $this->userValue && true === $this->nullOnEmpty) {
                return null;
            }
            return $this->userValue;
        } else {
            if ('' === $this->defaultValue && true === $this->nullOnEmpty) {
                return null;
            }
            return $this->defaultValue;
        }
    }

    /**
     * Returns the default value.
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Sets the default or database value for the
     *
     * @param mixed $value The value of the form field.
     *
     * @return self
     */
    public function setDefaultValue($value): FormElement
    {
        $this->defaultValue = $value;
        // If valid input was entered already, check if this value is different.
        if ($this->isValid) {
            $this->isChanged = ($this->defaultValue == $this->userValue);
        }
        return $this;
    }

    /**
     * Get the user set value, if valid.
     *
     * @return mixed
     */
    public function getUserValue()
    {
        if (null == $this->isValid) {
            $this->validate();
        }
        if (null !== $this->userValue && $this->isValid) {
            if ('' === $this->userValue && true === $this->nullOnEmpty) {
                return null;
            }
            return $this->userValue;
        } else {
            return '';
        }
    }

    /**
     * Sets a user value to be validated
     *
     * @param mixed $value
     *
     * @return self
     */
    public function setUserValue(string $value): FormElement
    {
        $this->userValue = $value;
        $this->isValid = null;
        return $this;
    }

    /**
     * Returns the user value regardless of validity.
     *
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->userValue;
    }

    /**
     * Validates the user value according to the validator defined for this element.
     */
    public function validate(): void
    {
        if (null === $this->isValid) {
            if (null === $this->userValue && null !== $this->defaultValue) {
                // There is a preset value, and its not being changed
                $this->isValid = true;
            } elseif (null != $this->validator && false != $this->validator) {
                if ('' === $this->userValue && false === $this->required) {
                    $this->isValid = true;
                    return;
                } elseif ('' === $this->userValue && true === $this->required) {
                    $this->isValid = false;
                    return;
                }
                $validator = $this->validator;
                $result = $validator::quickValidate($this->userValue, $this->filters, $this->constraints, $this->error);
                if ($result != null) {
                    $this->userValue = $result;
                    $this->isValid = true;
                } else {
                    $this->userValue = null;
                    $this->isValid = false;
                }
            } else {
                // This field does not get validated
                $this->isValid = true;
            }
        }
        // Check if the input is different from the default value
        if ($this->isValid && null !== $this->userValue) {
            $this->isChanged = ($this->defaultValue != $this->userValue);
        }
    }

    /**
     * Returns if the user submitted value for the field is valid, according to the validator set for the element.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (null === $this->isValid) {
            $this->validate();
        }
        return $this->isValid;
    }

    /**
     * Returns if the value was changed from the original. If the input is not valid, this will return false.
     *
     * @return bool
     */
    public function isChanged(): bool
    {
        if (null === $this->isValid) {
            $this->validate();
        }
        return $this->isChanged;
    }

    /**
     * This should be called when the element is added to a form to set a reference to the form
     *
     * @param Form $form A reference to the form this element is a member of
     *
     * @return FormElement
     */
    public function setFormReference(Form &$form): FormElement
    {
        $this->form = $form;
        return $this;
    }

    /**
     * Get the error message if this value is not valid.
     *
     * @return null|string
     */
    public function getError(): ?string
    {
        if (null === $this->isValid) {
            $this->validate();
        }
        return $this->error;
    }

    /**
     * Allows other systems such as the form's validator, to set this element as invalid. If no error message is
     * specified, the default error message will be set.
     *
     * @param string|null $message
     *
     * @return FormElement
     */
    public function setError(?string $message = null): FormElement
    {
        $this->isValid = false;
        $this->error = (null === $message) ? $this->defaultError : $message;
        return $this;
    }

    /**
     * After the form is saved, use commit to "save" the input values. This replaces the default value with the input
     * value, and resets isValid to null and isChanged to false.
     */
    public function commit(): self
    {
        if (null === $this->isValid) {
            $this->validate();
        }
        if ($this->isValid) {
            $this->defaultValue = $this->userValue;
        }
        $this->userValue = null;
        $this->isValid = null;
        $this->isChanged = false;
        return $this;
    }

    /**
     * Set a parameterized template that will determine how the parts of this element should be laid out.
     *
     * @param string $template The template
     *
     * @return $this
     */
    public function setTemplate(string $template): FormElement
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Shows the label as an html tag
     *
     * @return string
     */
    public function outputLabel(): string
    {
        if ($this->label != null) {
            $labelClass = count($this->labelClasses) > 0 ? ' class="' . implode(' ', $this->labelClasses) . '"' : '';
            return '<label' . $labelClass . ' for="' . $this->attributes['id'] . '">' . $this->label . '</label>';
        } else {
            return '';
        }
    }

    /**
     * Return the error message.
     *
     * @return string
     */
    public function outputError(): string
    {
        if ($this->error != null) {
            $errorClass = count($this->errorClasses) > 0 ? ' class="' . implode(' ', $this->errorClasses) . '"' : '';
            return '<div' . $errorClass . ' id="' . $this->attributes['id'] . '-errortext">' . $this->error . '</div>';
        }
        return '';
    }

    public function outputHelpText(): string
    {
        if ($this->helpText != null) {
            $helpClass = count($this->helpTextClasses) > 0 ? ' class="' . implode(
                    ' ',
                    $this->helpTextClasses
                ) . '"' : '';
            return '<div ' .
                $helpClass .
                ' id="' .
                $this->attributes['id'] .
                '-helptext">' .
                $this->helpText .
                '</div>';
        }
        return '';
    }

    /**
     * Returns the form element as an HTML tag
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
        if (count($this->classes) > 0) {
            $attributes[] = 'class="' . implode(' ', $this->classes) . '"';
        }
        if ($this->helpText != null) {
            $attributes[] = 'aria-describedby="' . $this->attributes['id'] . '-helptext"';
        }
        return '<input ' . implode(' ', $attributes) . ' />';
    }

    /**
     * Outputs just the value of the form element, without being in a form field.
     *
     * @return string
     */
    public function outputValue(): string
    {
        return '<span class="uneditable-field">' . $this->getValue() . '</span>';
    }

    /**
     * Outputs the form element and label if one is defined.
     *
     * @return string
     */
    public function __toString(): string
    {
        //var_dump('hi');exit();
        if ($this->template != null) {
            $template = $this->template;
        } elseif ($this->form != null) {
            $template = $this->form->getTemplate();
        } else {
            $template = '<div class="formgroup">:label: :field: :error: :helptext:</div><br />' . "\n";
        }

        $label = $this->outputLabel();
        if ($this->editable) {
            $element = $this->outputElement();
        } else {
            $element = $this->outputValue();
        }
        $name = $this->attributes['name'];

        $error = $this->outputError();
        if ($label . $element . $error != '') {
            $line = $template;
            $line = str_replace(':label:', $label, $line);
            $line = str_replace(':field:', $element, $line);
            $line = str_replace(':error:', $error, $line);
            $line = str_replace(':fieldname:', $name, $line);
            $line = str_replace(':helptext:', $this->outputHelpText(), $line);
        } else {
            $line = '';
        }
        return $line;
    }

    /**
     * Display the value as plain text instead of as a form element. This will cause the value not to be submitted with
     * the form. Being set uneditable does not offer any protection against editing the form in transit - if someone
     * can
     * guess the form value in transit, they can set a value that will be processed. For full protection against
     * editing, use the static value or static visible value form type.
     *
     * @return self
     */
    public function makeUneditable(): FormElement
    {
        $this->editable = false;
        return $this;
    }
}