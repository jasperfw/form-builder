<?php

namespace JasperFW\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement;

/**
 * Class CSRFToken
 *
 * Class for managing CSRF tokens specifically. This class has its own built in automatic validation for handling CSRF
 * tokens. This is expected to be generated by the Form class automatically depending on the csrf settings of the form.
 * Unexpected things may happen if it is added to a form manually.
 *
 * @package JasperFW\FormBuilder\FormElement
 */
class CSRFToken extends Hidden
{
    /** @var string The previous CSRF value */
    protected mixed $oldCSRF;
    /** @var string The new CSRF value */
    protected string $newCSRF;
    /** @var string|null The CSRF value that was submitted with the form */
    protected ?string $submittedCSRF = null;

    public function __construct(string $name, array $configuration = [], string $value = '')
    {
        parent::__construct($name, ['property' => false, 'required' => true], $value);
        $this->oldCSRF = $configuration['oldCSRF'];
        $this->newCSRF = $value;
    }

    public function setUserValue(string $value): FormElement
    {
        $this->submittedCSRF = $value;
        return $this;
    }

    /**
     * Make sure the submitted CSRF token matches the old token
     */
    public function validate(): void
    {
        $this->isValid = ($this->submittedCSRF === $this->oldCSRF);
        if (!$this->isValid) {
            $this->error = 'Invalid CSRF Token.';
        }
    }

    public function outputElement(): string
    {
        return '<input name="' . $this->attributes['name'] . '" type="hidden" value="' . $this->newCSRF . '" />';
    }
}
