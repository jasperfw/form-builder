<?php

namespace JasperFW\DataInterfaceTest\FormBuilder\FormElement;

use JasperFW\FormBuilder\FormElement\Radiobutton;
use JasperFW\Validator\Validator\TextString;
use PHPUnit\Framework\TestCase;

/**
 * Class RadiobuttonTest
 */
class RadiobuttonTest extends TestCase
{
    public function testConstruct()
    {
        $config = [
            'validator' => TextString::class,
            'formelement' => Radiobutton::class,
            'label' => 'Title:',
            'required' => false,
            'default' => 'defaultvalue',
        ];
        $options = [
            "First Radiobutton",
            "Second Radiobutton",
            "Third Radiobutton",
        ];
        $element = new Radiobutton('test', $config, $options);
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
        $this->assertEquals('', $element->outputLabel(), 'Incorrect label');

        $this->assertEquals(
            '<input type="radio" id="cb-0" name="test" value="0" checked /> <label  for="cb-0">First Radiobutton</label><br>
<input type="radio" id="cb-1" name="test" value="1" /> <label  for="cb-1">Second Radiobutton</label><br>
<input type="radio" id="cb-2" name="test" value="2" /> <label  for="cb-2">Third Radiobutton</label>',
            $element->outputElement(),
            'Incorrect Radiobuttons'
        );

        $this->assertEquals(
            '<div class="formgroup"> <input type="radio" id="cb-0" name="test" value="0" checked /> <label  for="cb-0">First Radiobutton</label><br>
<input type="radio" id="cb-1" name="test" value="1" /> <label  for="cb-1">Second Radiobutton</label><br>
<input type="radio" id="cb-2" name="test" value="2" /> <label  for="cb-2">Third Radiobutton</label>  </div><br />',
            trim($element->__toString()),
            'Incorrect toString'
        );
    }

}
