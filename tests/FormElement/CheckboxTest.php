<?php

namespace JasperFW\DataInterfaceTest\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement\Checkbox;
use JasperFW\Validator\Validator\TextString;
use PHPUnit\Framework\TestCase;

/**
 * Class FormElementTest
 *
 * TODO: Revisit this after making the test for select type
 */
class CheckboxTest extends TestCase
{
    public function testConstruct()
    {
        $this->markTestIncomplete('This is still under development');
        $config = [
            'validator' => TextString::class,
            'formelement' => Checkbox::class,
            'label' => 'Title:',
            'required' => false,
            'default' => 'defaultvalue',
        ];
        $element = new Checkbox('test', $config);
        $this->assertEquals(
            TextString::class,
            $element->__get('validator'),
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
            '<input id="test-field" class="test-field" value="uservalue" name="test" type="checkbox" style="color: black;" />',
            $element->outputElement(),
            'Incorrect element'
        );
        $this->assertEquals(
            $element->outputLabel() . ' ' . $element->outputElement() . ' <br />',
            trim($element->__toString()),
            'Incorrect toString'
        );
    }
}
