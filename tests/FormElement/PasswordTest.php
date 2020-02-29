<?php

namespace JasperFW\DataInterfaceTest\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement\Password;
use JasperFW\Validator\Validator\TextString;
use PHPUnit\Framework\TestCase;

/**
 *
 * Class PasswordTest
 */
class PasswordTest extends TestCase
{

    public function testConstruct()
    {
        $config = [
            'validator' => TextString::class,
            'formelement' => Password::class,
            'label' => 'Title:',
            'required' => false,
            'default' => '123',
        ];
        $element = new Password('test', $config);
        $this->assertEquals(
            $element->__get('validator'),
            TextString::class,
            'The correct validator was not returned'
        );
        $element->setAttribute('style', 'color: black;');
        $element->setUserValue('password123');
        $element->setId('test-field');
        $element->addClass('test-field');
        $element->setDefaultValue('defaultvalue');
        $this->assertTrue($element->isValid());
        $this->assertEquals('password123', $element->getUserValue(), 'Incorrect user value');
        $this->assertEquals('defaultvalue', $element->getDefaultValue(), 'Incorrect default value.');
        $this->assertEquals('password123', $element->getRawValue(), 'Incorrect raw value');
        $this->assertEquals('<label for="test-field">Title:</label>', $element->outputLabel(), 'Incorrect label');
        $this->assertEquals(
            '<input id="test-field" value="password123" name="test" type="password" style="color: black;" class="test-field" />',
            $element->outputElement(),
            'Incorrect element'
        );
        $this->assertEquals(
            '<div class="formgroup"><label for="test-field">Title:</label> <input id="test-field" value="password123" name="test" type="password" style="color: black;" class="test-field" />  </div><br />',
            trim($element->__toString()),
            'Incorrect toString'
        );
    }
}
