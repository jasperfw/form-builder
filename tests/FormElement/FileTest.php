<?php

namespace JasperFW\FormBuilderTest\FormElement;

use JasperFW\FormBuilder\FormElement\File;
use PHPUnit\Framework\TestCase;

/**
 * Class FileTest
 */
class FileTest extends TestCase
{
    public function testConstruct()
    {
        $config = [
            'label' => 'Attach File:',
            'required' => false,
            'default' => 'defaultvalue',
        ];

        $element = new File('test', $config, 'DESCRIPTION');
        $this->assertEquals(
            '<input id="test" value="defaultvalue" name="test" type="file" />',
            $element->outputElement(),
            'Incorrect element'
        );
    }
}
