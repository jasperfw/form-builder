<?php

namespace JasperFW\DataInterfaceTest\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement\Numberfield;
use JasperFW\Validator\Validator\Number;
use PHPUnit\Framework\TestCase;

/**
 * Class NumberfieldTest
 */
class NumberfieldTest extends TestCase
{
    public function testConstruct()
    {
        $config = [
            'validator' => Number::class,
            'formelement' => NumberField::class,
            'label' => 'Numberfield:',
            '',
        ];

        $element = new Numberfield('test', $config, '0987654321');
        $this->assertEquals(
            Number::class,
            $element->__get('validator'),
            'The correct validator was not returned'
        );

        $element->setAttribute('style', 'color: black;');
        $element->setId('test-field');
        $element->addClass('test-field');
        $element->setDefaultValue('1234598765');

        $element->setUserValue('1234567890');
        $this->assertEquals('1234567890', $element->getUserValue(), 'Incorrect value');

        $this->assertEquals('1234598765', $element->getDefaultValue(), 'Incorrect default value.');
        $this->assertEquals('1234567890', $element->getRawValue(), 'Incorrect raw value');

        $this->assertEquals('<label for="test-field">Numberfield:</label>', $element->outputLabel(), 'Incorrect label');
        $this->assertEquals(
            '<input id="test-field" value="1234567890" name="test" type="number" style="color: black;" class="test-field" />',
            $element->outputElement(),
            'Incorrect element'
        );
        $this->assertEquals(
            '<div class="formgroup"><label for="test-field">Numberfield:</label> <input id="test-field" value="1234567890" name="test" type="number" style="color: black;" class="test-field" />  </div><br />',
            trim($element->__toString()),
            'Incorrect toString'
        );
    }
}
