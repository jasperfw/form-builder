<?php

namespace JasperFW\DataInterfaceTest\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement\Checkboxgroup;
use JasperFW\Validator\Validator\TextString;
use PHPUnit\Framework\TestCase;

/**
 * Class CheckboxgroupTest
 */
class CheckboxgroupTest extends TestCase
{
    public function testConstruct()
    {
        $config = [
            'validator' => TextString::class,
            'formelement' => Checkboxgroup::class,
            'label' => 'Title:',
            'required' => false,
            'default' => 'defaultvalue',
        ];
        $options = [
            "First Checkboxgroup",
            "Second Checkboxgroup",
        ];
        $element = new Checkboxgroup('test', $config, $options);
        $this->assertEquals(TextString::class, $element->__get('validator'), 'The correct validator was not returned');
        $element->setAttribute('style', 'color: black;');
        $element->setUserValue('uservalue');
        $element->setId('test-field');
        $element->addClass('test-field');
        $element->setDefaultValue('defaultvalue');
//        $this->assertTrue($element->isValid());
        $this->assertEquals('uservalue', $element->getUserValue(), 'Incorrect user value');
        $this->assertEquals('defaultvalue', $element->getDefaultValue(), 'Incorrect default value.');
        $this->assertEquals('uservalue', $element->getRawValue(), 'Incorrect raw value');
        $this->assertEquals('', $element->outputLabel(), 'Incorrect label');
        $this->assertEquals(
            '<input id="cb-0" type="checkbox" name="test[]" value="0" checked="checked" /><label  for="cb-0">First Checkboxgroup</label><input id="cb-1" type="checkbox" name="test[]" value="1" /><label  for="cb-1">Second Checkboxgroup</label>',
            $element->outputElement(),
            'Incorrect Checkboxgroup'
        );
        $this->assertEquals(
            '<div class="formgroup"> <input id="cb-0" type="checkbox" name="test[]" value="0" checked="checked" /><label  for="cb-0">First Checkboxgroup</label><input id="cb-1" type="checkbox" name="test[]" value="1" /><label  for="cb-1">Second Checkboxgroup</label>  </div><br />',
            trim($element->__toString()),
            'Incorrect toString'
        );
    }
}
