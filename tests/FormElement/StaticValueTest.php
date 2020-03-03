<?php

namespace JasperFW\FormBuilderTest\FormElement;

use JasperFW\FormBuilder\FormElement\StaticValue;
use JasperFW\Validator\Validator\TextString;
use PHPUnit\Framework\TestCase;

/**
 * Class FormElementTest
 */
class StaticValueTest extends TestCase
{
    public function testConstruct()
    {
        $config = [
            'validator' => TextString::class,
            'formelement' => StaticValue::class,
            'label' => 'Title:',
            'required' => false,
            'default' => 'defaultvalue',
            'onClick' => 'nothing',
        ];
        $element = new StaticValue('test', $config);
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
        $this->assertEquals('', $element->getUserValue(), 'It should not be possible to set a user value');
        $this->assertEquals('defaultvalue', $element->getDefaultValue(), 'Incorrect default value.');
        $this->assertEquals(null, $element->getRawValue(), 'Incorrect raw value');
        $this->assertEquals('', $element->outputLabel(), 'Incorrect label');
        $this->assertEquals('', $element->outputElement(), 'Incorrect element');
        $this->assertEquals('', trim($element->__toString()), 'Incorrect toString');
    }
}
