<?php

namespace JasperFW\FormBuilderTest\FormElement;

use JasperFW\FormBuilder\FormElement\Select;
use JasperFW\Validator\Validator\TextString;
use PHPUnit\Framework\TestCase;

/**
 * Class SelectTest
 */
class SelectTest extends TestCase
{
    public function testConstruct()
    {
        $config = [
            'validator' => TextString::class,
            'formelement' => Select::class,
            'label' => 'Title:',
            'required' => false,
            'default' => 'defaultvalue',
            'values' => [
                'uservalue' => 'First Select',
                'two' => 'Second Select',
                'three' => 'Third Select',
                'four' => 'Fourth Select',
            ],
        ];
        $element = new Select('test', $config);
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
            '<select id="test-field" name="test" style="color: black;" class="test-field">
<option value="uservalue" selected="selected">First Select</option>
<option value="two">Second Select</option>
<option value="three">Third Select</option>
<option value="four">Fourth Select</option>
</select>',
            $element->outputElement(),
            'Incorrect element'
        );
        $this->assertEquals(
            '<div class="formgroup"><label for="test-field">Title:</label> <select id="test-field" name="test" style="color: black;" class="test-field">
<option value="uservalue" selected="selected">First Select</option>
<option value="two">Second Select</option>
<option value="three">Third Select</option>
<option value="four">Fourth Select</option>
</select>  </div><br />',
            trim($element->__toString()),
            'Incorrect toString'
        );
    }
}
