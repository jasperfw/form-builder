<?php

namespace JasperFW\FormBuilder;

use Exception;
use JasperFW\Core\Callback\CallbackDefinition;
use JasperFW\Core\Collection\Collection;
use JasperFW\Core\Exception\CollectionException;
use JasperFW\FormBuilder\Exception\FormStructureException;
use JasperFW\FormBuilder\Exception\NoSuchElementException;
use JasperFW\FormBuilder\FormElement\CSRFToken;
use JasperFW\FormBuilder\FormElement\Hidden;
use JasperFW\FormBuilder\FormElement\HiddenVisible;
use JasperFW\FormBuilder\FormElement\StaticValue;
use JasperFW\Validator\Exception\BadDefinitionException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Form
 *
 * Base class of the form building system. A form is a collection of input types and/or form sections for display. By
 * using the form building system, a single mechanism for managing forms can be created.
 *
 * @package JasperFW\FormBuilder
 * @property FormElement[] $members
 * @property FormElement   csrfToken
 */
class Form extends Collection
{
    /** @var string Use this to specify the line break template */
    const TEMPLATE_BR = '1';
    /** @var string Use this to specify the div template */
    const TEMPLATE_DIV = '2';
    /** @var string Use this to specify the div class template */
    const TEMPLATE_DIV_CLASS = '3';
    /** @var string Use this to specify the default input template */
    const TEMPLATE_DEFAULT = '0';
    /** @var array All of the forms in the current request, because why not (and for CSRF stuff) */
    protected static $forms = [];
    /**
     * @var array The structure of the form, used for autocreating the form elements.
     */
    protected static $structure;
    protected static $templates = [
        0 => ':label: :field:',
        1 => ':label: :field: :error:<br />',
        2 => '<div id="div_:fieldname:">:label: :field: :helptext: :error:</div>',
        3 => '<div class=":fieldname:">:label: :field: :helptext: :error:</div>',
    ];
    /**
     * @var string The name of this form, in case there are multiple forms on the page. This will be used in naming the
     *      CSRF token for the form.
     */
    protected $name;
    /** @var CallbackDefinition[] Callbacks to call after populate */
    protected $postPopulateCallbacks = [];
    /** @var array Array of attributes to add to the form element */
    protected $attributes = [
        'accept-charset' => 'UNKNOWN',
        'action' => '',
        'autocomplete' => 'on',
        'enctype' => 'application/x-www-form-urlencoded',
        'method' => 'get',
        'class' => '',
    ];
    /** @var null|string Template for a full form element, label, help text, etc */
    protected $template = null;
    /** @var null|string Template for a form element's label */
    protected $labelTemplate = null;
    /** @var null|string Template for a form element's INPUT, SELECT, etc tag */
    protected $fieldTemplate = null;
    /** @var null|string Template for a form element error message */
    protected $errorTemplate = null;
    /** @var array A list of errors, primarily from validation. */
    protected $errors = [];
    /** @var bool True if CSRF protection is enabled. */
    protected $csrfEnabled = true;
    /** @var LoggerInterface A logger to record issues. */
    protected $logger;
    /** @var string[] Array of classes to be added to all element tags in the form */
    protected $allElementsClasses = [];
    /** @var string[] Array of classes to be added to all help text spans in the form */
    protected $allHelpTextClasses = [];
    /** @var string[] Array of classes to be added to all labels in the form */
    protected $allLabelClasses = [];
    /** @var string[] Array of classes to be added to all error spans in the form */
    protected $allErrorClasses = [];
    /** @var bool True if the CSRF token has been initialized by a form in this request. */
    private $csrfTokenInitialized = false;
    /** @var string The previous CSRF token to validate against */
    private $oldCsrfToken;
    /** @var string The new CSRF token for forms displayed in this request */
    private $newCsrfToken;

    /**
     * Creates a new form object from the passed structure array. See the documentation for details about valid
     * options that can be in the structure array.
     *
     * @param array $structure
     *
     * @return Form
     * @throws FormStructureException
     * @throws Exception
     */
    public static function factory(array $structure): Form
    {
        $form = new Form();
        $form->_init($structure);
        return $form;
    }

    /**
     * Creates a form object,optionally setting the action and method.
     *
     * @param null|LoggerInterface $logger
     *
     * @throws FormStructureException
     * @throws Exception
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        parent::__construct();
        $this->setTemplate(static::TEMPLATE_DEFAULT);
        // Set a placeholder logger
        $this->logger = $logger ?? new NullLogger();
        // If there is a structure defined, init the form
        if (isset(static::$structure)) {
            $this->_init(static::$structure);
        }
        // Make sure a name is specified
        if (null === $this->name) {
            $this->name = 'form-' . (count(self::$forms) + 1);
        }
        // Add the form to the array
        self::$forms[$this->name] = $this;
        // Make sure CSRF token is initialized if enabled
        $this->initializeCSRFToken();
        $this->createCSRFInput();
    }

    /**
     * Set or change the logger for error messages and other debug output generated by the form.
     *
     * @param LoggerInterface $logger
     *
     * @return Form
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Returns the form element with the specified name.
     *
     * @param string $name
     *
     * @return FormElement
     * @throws NoSuchElementException
     * @throws CollectionException
     */
    public function __get(string $name): FormElement
    {
        if (isset($this->members[$name])) {
            return $this->getItem($name);
        }
        throw new NoSuchElementException('There is no form element ' . $name);
    }

    /**
     * Set an attribute for the form's html tag. Replaces the existing value.
     *
     * @param string $name  The name of the attribute
     * @param string $value The new value of the attribute
     *
     * @return Form
     */
    public function setAttribute(string $name, string $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Set the id of the form.
     *
     * @param string $id
     *
     * @return Form
     */
    public function setId(string $id): self
    {
        $this->attributes['id'] = $id;
        return $this;
    }

    /**
     * Add the specified class to the class attribute for the form.
     *
     * @param string $value A class to add
     *
     * @return Form
     */
    public function addFormClass(string $value): self
    {
        $class = trim($this->attributes['class']);
        $this->attributes['class'] = $class . ' ' . trim($value);
        return $this;
    }

    public function addItem($object, ?string $key = null): void
    {
        // Do nothing
    }

    public function addItemAfter($object, string $previous_key, ?string $key = null): void
    {
        // Do nothing
    }

    public function addItemBefore($object, string $latter_key, ?string $key = null): void
    {
        // Do nothing
    }

    /**
     * Add a form element or fieldset to the form. The passed element can be an array or a FormElement object.
     *
     * @param FormElement $formElement The form element to add.
     * @param string|null $key         The key is typically going to be ignored.
     *
     * @return Form
     * @throws Exception
     */
    public function addFormElement(FormElement $formElement, string $key = null): self
    {
        $formElement->setFormReference($this);
        $this->addClassesToElementParts($formElement);
        parent::addItem($formElement, $key ?? $formElement->name);
        return $this;
    }

    /**
     * Add a form element after an existing element
     *
     * @param FormElement $formElement The new element
     * @param string      $previousKey The key to add the element after
     * @param string|null $key         The key of the new element. If null will use the name of the form element object
     *
     * @return Form
     * @throws CollectionException
     */
    public function addFormElementAfter(FormElement $formElement, string $previousKey, ?string $key = null): self
    {
        $formElement->setFormReference($this);
        $this->addClassesToElementParts($formElement);
        parent::addItemAfter($formElement, $previousKey, $key ?? $formElement->name);
        return $this;
    }

    /**
     * Add a form element before an existing element
     *
     * @param FormElement $formElement The new element
     * @param string      $latterKey   The key to add the element before
     * @param string|null $key         The key of the new element. If null will use the name of the form element object
     *
     * @return Form
     * @throws CollectionException
     */
    public function addFormElementBefore(FormElement $formElement, string $latterKey, ?string $key = null): self
    {
        $formElement->setFormReference($this);
        $this->addClassesToElementParts($formElement);
        parent::addItemBefore($formElement, $latterKey, $key ?? $formElement->name);
        return $this;
    }

    /**
     * Add a class to all elements in the form
     *
     * @param string $class The class to add
     *
     * @return $this
     */
    public function addClassToElements(string $class): self
    {
        if (!in_array($class, $this->allElementsClasses)) {
            $this->allElementsClasses[] = $class;
            foreach ($this->members as $member) {
                $member->addClass($class);
            }
        }
        return $this;
    }

    /**
     * Add a class to all labels in the form
     *
     * @param string $class The class to add
     *
     * @return $this
     */
    public function addClassToLabels(string $class): self
    {
        if (!in_array($class, $this->allLabelClasses)) {
            $this->allLabelClasses[] = $class;
            foreach ($this->members as $member) {
                $member->addLabelClass($class);
            }
        }
        return $this;
    }

    /**
     * Add a class to all help text in the form
     *
     * @param string $class The class to add
     *
     * @return $this
     */
    public function addClassToElementHelpText(string $class): self
    {
        if (!in_array($class, $this->allHelpTextClasses)) {
            $this->allHelpTextClasses[] = $class;
            foreach ($this->members as $member) {
                $member->addHelpTextClass($class);
            }
        }
        return $this;
    }

    /**
     * Add a class to all of the error message containers
     *
     * @param string $class The class to add
     *
     * @return $this
     */
    public function addClassToElementErrors(string $class): self
    {
        if (!in_array($class, $this->allErrorClasses)) {
            $this->allErrorClasses[] = $class;
            foreach ($this->members as $member) {
                $member->addErrorClass($class);
            }
        }
        return $this;
    }

    /**
     * Puts data into the form. Intended to be used to incorporate submitted user data into the form.
     *
     * @param string[] $data         The data to insert
     * @param bool     $is_user_data True if the data is user data, false if it is default or db data.
     *
     * @return Form
     */
    public function populate(array $data, bool $is_user_data = true): self
    {
        foreach ($data as $name => $value) {
            if (isset($this->members[$name])) {
                if ($is_user_data) {
                    $this->members[$name]->setUserValue($value);
                } else {
                    $this->members[$name]->setDefaultValue($value);
                }
            } else {
                // If the data is coming from a database, the name might be the $dbName
                foreach ($this->members as $member) {
                    if ($member->getDBName() === $name) {
                        if ($is_user_data) {
                            $member->setUserValue($value);
                        } else {
                            $member->setDefaultValue($value);
                        }
                    }
                }
            }
        }
        foreach ($this->postPopulateCallbacks as $callback) {
            $callback->execute();
        }
        return $this;
    }

    /**
     * Register a function to be called after the populate function is run
     *
     * @param CallbackDefinition $callback
     *
     * @return Form
     */
    public function registerPopulateCallback(CallbackDefinition $callback): self
    {
        $this->postPopulateCallbacks[] = $callback;
        return $this;
    }

    /**
     * Determines if the form is valid by iterating through the form elements and testing each one if there is a
     * validator assigned.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        $isValid = true;
        foreach ($this->members as $name => $member) {
            if (false === $member->isValid()) {
                $isValid = false;
                $error_message = $member->getLabel() . ' is not valid: ' . $member->getError();
                $this->logger->notice($error_message);
                $this->errors[] = $error_message;
            }
        }
        return $isValid;
    }

    /**
     * Checks if any of the form fields have been changed from the default values. This requires that a new value was
     * entered and determined to be valid.
     *
     * @return bool
     */
    public function isChanged(): bool
    {
        $is_changed = false;
        foreach ($this->members as $name => $member) {
            if (true === $member->isChanged()) {
                $is_changed = true;
            }
        }
        return $is_changed;
    }

    /**
     * If the new data in the form has been saved to the database or otherwise processed, running this function will
     * update the provided values and reset the form to represent the updated object.
     */
    public function commit(): void
    {
        foreach ($this->members as $name => $member) {
            $member->commit();
        }
    }

    /**
     * Return an array of error messages for invalid fields.
     *
     * @return array
     * @throws Exception
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns the errors stored in the object as a string.
     *
     * @return bool|string The errors, or false if no errors were generated.
     * @throws Exception
     */
    public function getErrorString(): string
    {
        $errors = $this->getErrors();
        if (count($errors) == 0) {
            return false;
        }
        return implode(' ', $errors);
    }

    /**
     * Gets the valid data out of the form. If this is being used for a database query, pass true to get the database
     * column names in place of the field names.
     *
     * @param bool $for_database Set to true if this is for use with a DB query
     *
     * @return array
     * @throws BadDefinitionException
     */
    public function getData(bool $for_database = false): array
    {
        $data = [];
        foreach ($this->members as $key => $member) {
            if ($member->isProperty()) {
                if (true === $for_database) {
                    switch ($member->getType()) {
                        case 'int':
                            $data[$member->getDBName()] = (int)$member->getValue();
                            break;
                        case 'float':
                            $data[$member->getDBName()] = number_format((float)$member->getValue(), 2);
                            break;
                        case 'string':
                        default:
                            $data[$member->getDBName()] = $member->getValue();
                    }
                } else {
                    $data[$key] = $member->getValue();
                }
            }
        }
        return $data;
    }

    /**
     * Clear the user submitted data from the form.
     */
    public function resetData(): void
    {
        foreach ($this->members as $key => $member) {
            $member->userValue = null;
        }
    }

    /**
     * Convert the form data to an array. This is an alias of getData(). If you want to get data for use in a DB insert
     * or update query, use getData() directly, passing TRUE for the argument.
     *
     * @param bool $for_database True to set the keys to the dbName instead of the raw field name.
     *
     * @return array
     * @throws BadDefinitionException
     */
    public function toArray(bool $for_database = false): array
    {
        return $this->getData($for_database);
    }

    /**
     * Converts the field into a hidden field with the currently set value as the user value of the new object.
     *
     * @param string $field_name
     *
     * @return Form
     */
    public function makeHidden(string $field_name): self
    {
        if (!$this->exists($field_name)) {
            return $this;
        }
        $old = $this->members[$field_name];
        $new = new Hidden($field_name);
        $new->setUserValue($old->userValue);
        $this->members[$field_name] = $new;
        return $this;
    }

    /**
     * Converts a field to a hidden visible field - this is a hidden field that still outputs a label and the set value,
     * without it being editable.
     *
     * @param string $field_name
     *
     * @return Form
     * @throws BadDefinitionException
     */
    public function makeHiddenVisible(string $field_name): self
    {
        if (!$this->exists($field_name)) {
            return $this;
        }
        $old = $this->members[$field_name];
        $new = new HiddenVisible($field_name);
        $new->setDefaultValue($old->getValue());
        $new->setUserValue($old->userValue);
        $this->members[$field_name] = $new;
        return $this;
    }

    /**
     * Make all fields uneditable
     */
    public function makeUneditable(): self
    {
        $field_names = $this->keys();
        foreach ($field_names AS $field_name) {
            $this->members[$field_name]->makeUneditable();
        }
        return $this;
    }

    /**
     * Make the current field a static value so that it can not be viewed or changed by the user.
     *
     * @param string $field_name
     *
     * @return Form
     */
    public function makeStatic(string $field_name): self
    {
        if (!$this->exists($field_name)) {
            return $this;
        }
        $old = $this->members[$field_name];
        $new = new StaticValue($field_name);
        $new->setUserValue($old->userValue);
        $this->members[$field_name] = $new;
        return $this;
    }

    /**
     * Set the form to be multipart/form-data and the method to post so that uploads are possible.
     */
    public function enableUploads(): self
    {
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('method', 'post');
        return $this;
    }

    /**
     * Get the currently defined template.
     *
     * @return null|string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * Define how lines should end. Set to END_NONE if the form should be displayed inline or if CSS is handling it. Set
     * to END_BR if each form element should be followed with a <br />. Set to END_DIV if each label and element should
     * be in a div.
     *
     * @param int $template
     */
    public function setTemplate(int $template): void
    {
        switch ($template) {
            case self::TEMPLATE_DEFAULT:
            case self::TEMPLATE_DIV_CLASS:
            case self::TEMPLATE_DIV:
            case self::TEMPLATE_BR:
                $this->template = static::$templates[$template];
                break;
            default:
                $this->template = $template;
        }
        $this->$template = $template;
    }

    /**
     * Get an array of field names for the form fields to retrieve corresponding values from the database. This will
     * return only those fields that are set as properties of the object this form represents.
     *
     * @return array
     */
    public function getDbSelectFieldNames(): array
    {
        $return = [];
        foreach ($this->members as $member) {
            if ($member->isProperty()) {
                $return[] = $member->getSelectName();
            }
        }
        return $return;
    }

    /**
     * Output the opening tag of the form.
     */
    public function openForm(): string
    {
        $attributes = [];
        foreach ($this->attributes as $name => $value) {
            $attributes[] = $name . '="' . $value . '"';
        }
        return '<form ' . implode(' ', $attributes) . ' >' . "\n";
    }

    /**
     * Output the closing tag of the form.
     */
    public function closeForm(): string
    {
        return '</form>' . "\n";
    }

    /**
     * Output the entire form, showing the opening and closing tags, and calling the to string methods of each element.
     */
    public function __toString(): string
    {
        $return = '';
        $return .= $this->openForm();
        /** @var FormElement $member */
        foreach ($this->members as $member) {
            $return .= $member->__toString();
        }
        $return .= $this->closeForm();
        return $return;
    }

    /**
     * Set up the CSRF tokens
     */
    protected function initializeCSRFToken(): void
    {
        // Make sure CSRF is enabled on this form
        if (!$this->csrfEnabled) {
            return;
        }
        // Only run once
        if ($this->csrfTokenInitialized) {
            return;
        }
        $this->oldCsrfToken = (isset($_SESSION[$this->name . '_csrf_token'])) ? $_SESSION[$this->name .
        '_csrf_token'] : null;
        $_SESSION[$this->name . '_csrf_token'] = (string)$this->newCsrfToken = (rand(100000, 999999));
        $this->csrfTokenInitialized = true;
    }

    /**
     * Set up the form from a structure, either via factory or
     *
     * @param array $structure
     *
     * @throws FormStructureException
     * @throws Exception
     */
    protected function _init(array $structure): void
    {
        // Determine if CSRF should be enabled
        $this->csrfEnabled = true;
        if (isset($structure['_meta_']['csrf'])) {
            if ($structure['_meta_']['csrf'] == false) {
                $this->csrfEnabled = false;
            }
            unset($structure['_meta_']['csrf']);
        }
        // Set the name of the form if defined
        if (isset($structure['_meta_']['name'])) {
            $this->name = $structure['_meta_']['name'];
            unset($structure['_meta_']['name']);
        }
        // Set a form element template if one is specified
        if (isset($structure['_meta_']['template'])) {
            $this->template = $structure['_meta_']['template'];
            unset($structure['_meta_']['template']);
        }
        // Get any classes that should be added
        if (isset($structure['_meta_']['elementClasses'])) {
            $classes = $structure['_meta_']['elementClasses'];
            if (!is_array($classes)) {
                $classes = [$classes];
            }
            $this->allElementsClasses = $classes;
            unset($structure['_meta_']['elementClasses']);
        }
        if (isset($structure['_meta_']['labelClasses'])) {
            $classes = $structure['_meta_']['labelClasses'];
            if (!is_array($classes)) {
                $classes = [$classes];
            }
            $this->allLabelClasses = $classes;
            unset($structure['_meta_']['labelClasses']);
        }
        if (isset($structure['_meta_']['helpClasses'])) {
            $classes = $structure['_meta_']['helpClasses'];
            if (!is_array($classes)) {
                $classes = [$classes];
            }
            $this->allHelpTextClasses = $classes;
            unset($structure['_meta_']['helpClasses']);
        }
        if (isset($structure['_meta_']['errorClasses'])) {
            $classes = $structure['_meta_']['errorClasses'];
            if (!is_array($classes)) {
                $classes = [$classes];
            }
            $this->allErrorClasses = $classes;
            unset($structure['_meta_']['errorClasses']);
        }
        // Look for a _meta_ element in the array to set form attributes
        if (isset($structure['_meta_'])) {
            foreach ($structure['_meta_'] as $key => $value) {
                $this->setAttribute($key, $value);
            }
            unset($structure['_meta_']);
        }
        // Go through the individual fields and create form elements for them.
        foreach ($structure as $field_name => $field) {
            if (isset($field['formelement'])) {
                $field_class = $field['formelement'];
                /** @var FormElement $field_object */
                $field_object = new $field_class($field_name, $field);
                $field_object->setFormReference($this);
                $this->addFormElement($field_object, $field_name);
            } else {
                // If a form element is not set, create a static value field for the entry
                $field_object = new StaticValue($field_name, $field);
                $field_object->setFormReference($this);
                $this->addFormElement($field_object, $field_name);
            }
        }
    }

    /**
     * Create the input field for the CSRF token
     *
     * @throws Exception
     */
    protected function createCSRFInput(): void
    {
        if (!$this->csrfEnabled) {
            return;
        }
        $configuration = [
            'oldCSRF' => $this->oldCsrfToken,
        ];
        $this->addFormElement(new CSRFToken('csrfToken', $configuration, $this->newCsrfToken));
    }

    /**
     * Called when a new element is added to the form, adds any classes that are specified to be added to all element to
     * it if they are not already set.
     *
     * @param FormElement $element The element to have the classes added to
     */
    protected function addClassesToElementParts(FormElement $element): void
    {
        foreach ($this->allElementsClasses as $class) {
            $element->addClass($class);
        }
        foreach ($this->allLabelClasses as $class) {
            $element->addLabelClass($class);
        }
        foreach ($this->allHelpTextClasses as $class) {
            $element->addHelpTextClass($class);
        }
        foreach ($this->allErrorClasses as $class) {
            $element->addErrorClass($class);
        }
    }
}