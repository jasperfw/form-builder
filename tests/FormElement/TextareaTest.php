<?php

namespace JasperFW\DataInterfaceTest\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement\Textarea;
use JasperFW\Validator\Validator\TextString;
use PHPUnit\Framework\TestCase;

/**
 * Class TextareaTest
 */
class TextareaTest extends TestCase
{
    public function testConstruct()
    {
        $config = [
            'validator' => TextString::class,
            'formelement' => Textarea::class,
            'label' => 'Title:',
            'required' => false,
            'default' => 'defaultvalue',
        ];
        $element = new Textarea('test', $config);
        $this->assertEquals(
            $element->__get('validator'),
            TextString::class,
            'The correct validator was not returned'
        );
        $element->setAttribute('style', 'color: black;');
        $element->setUserValue('The quick brown fox jumps over the lazy dog');
        $element->setId('test-field');
        $element->addClass('test-field');
        $element->setDefaultValue('defaultvalue');
        $this->assertTrue($element->isValid());
        $this->assertEquals(
            'The quick brown fox jumps over the lazy dog',
            $element->getUserValue(),
            'Incorrect user value'
        );
        $this->assertEquals('defaultvalue', $element->getDefaultValue(), 'Incorrect default value.');
        $this->assertEquals(
            'The quick brown fox jumps over the lazy dog',
            $element->getRawValue(),
            'Incorrect raw value'
        );
        $this->assertEquals('<label for="test-field">Title:</label>', $element->outputLabel(), 'Incorrect label');
        $this->assertEquals(
            '<textarea id="test-field" name="test" style="color: black;" class="test-field">
The quick brown fox jumps over the lazy dog</textarea>',
            $element->outputElement(),
            'Incorrect element'
        );
        $this->assertEquals(
            '<div class="formgroup"><label for="test-field">Title:</label> <textarea id="test-field" name="test" style="color: black;" class="test-field">
The quick brown fox jumps over the lazy dog</textarea>  </div><br />',
            trim($element->__toString()),
            'Incorrect toString'
        );
    }
}
