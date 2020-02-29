<?php

namespace JasperFW\DataInterfaceTest\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement\Textfield;
use JasperFW\Validator\Validator\TextString;
use PHPUnit\Framework\TestCase;

/**
 * Class FormElementTest
 */
class TextfieldTest extends TestCase
{
    public function testConstruct()
    {
        $config = [
            'validator' => TextString::class,
            'formelement' => Textfield::class,
            'label' => 'Title:',
            'required' => false,
            'default' => 'defaultvalue',
        ];
        $element = new Textfield('test', $config);
        $this->assertEquals(
            $element->__get('validator'),
            TextString::class,
            'The correct validator was not returned'
        );
        $element->setAttribute('style', 'color: black;');
        $element->setUserValue('uservalue');
        $element->setId('test-field');
        $element->addClass('test-field');
        $element->setDefaultValue('defaultvalue');
        $this->assertTrue($element->isValid());
        $this->assertEquals('uservalue', $element->getUserValue(), 'Incorrect user value');
        $this->assertEquals('defaultvalue', $element->getDefaultValue(), 'Incorrect default value.');
        $this->assertEquals('uservalue', $element->getRawValue(), 'Incorrect raw value');
        $this->assertEquals('<label for="test-field">Title:</label>', $element->outputLabel(), 'Incorrect label');
        $this->assertEquals(
            '<input id="test-field" value="uservalue" name="test" type="text" style="color: black;" class="test-field" />',
            $element->outputElement(),
            'Incorrect element'
        );
        $this->assertEquals(
            '<div class="formgroup"><label for="test-field">Title:</label> <input id="test-field" value="uservalue" name="test" type="text" style="color: black;" class="test-field" />  </div><br />',
            trim($element->__toString()),
            'Incorrect toString'
        );
    }
}
