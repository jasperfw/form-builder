<?php

namespace JasperFW\DataInterfaceTest\FormBuilder;

use Exception;
use JasperFW\FormBuilder\Exception\FormStructureException;
use JasperFW\FormBuilder\Form;
use JasperFW\FormBuilder\FormElement\Select;
use JasperFW\FormBuilder\FormElement\Submit;
use JasperFW\FormBuilder\FormElement\Textfield;
use JasperFW\Validator\Validator\Alpha;
use JasperFW\Validator\Validator\TextString;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use TypeError;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('xdebug.var_display_max_depth', '7');
ini_set('xdebug.var_display_max_children', '256');
ini_set('xdebug.var_display_max_data', '1024');

/**
 * Class FormTest
 */
class FormTest extends TestCase
{
    private $old_csrf = '12345';

    /**
     * @return Form
     * @throws FormStructureException
     * @throws Exception
     */
    public function testFactory()
    {
        $structure = [
            '_meta_' => [
                'action' => 'ticket/create',
                'method' => 'post',
                'csrf' => false,
            ],
            'title' => [
                'validator' => TextString::class,
                'formelement' => Textfield::class,
                'label' => 'Title:',
            ],
            'type' => [
                'validator' => Alpha::class,
                'formelement' => Select::class,
                'label' => 'Type:',
            ],
            'save' => [
                'validator' => false,
                'label' => '&nbsp;',
                'formelement' => Submit::class,
                'default' => 'Save',
                'dbname' => false,
                'property' => false,
            ],
        ];
        $form = Form::factory($structure);
        $this->assertTrue(is_a($form, '\JasperFW\FormBuilder\Form'));
        return $form;
    }

    /**
     * @throws FormStructureException
     * @throws Exception
     */
    public function testBadFactory()
    {
        $this->expectException(TypeError::class);
        /** @noinspection PhpParamsInspection */
        Form::factory('notanarray');
    }

    /**
     * This tests constructor options not covered with the factory tests.
     *
     * @throws FormStructureException*@throws Exception
     * @throws Exception
     */
    public function testConstructOptions()
    {
        new Form(new NullLogger());
        $this->markTestIncomplete('Still working on this');
    }

    /**
     * @depends testFactory
     *
     * @param Form $form
     *
     * @throws Exception
     */
    public function testForm($form)
    {
        $form->setId('testForm');
        $form->addFormClass('form-class');

        $defaultdata = ['title' => 'blah', 'type' => 'default'];
        $userdata = ['title' => 'newtitle', 'type' => 'invalid-data'];
        $expecteddata = ['title' => 'newtitle', 'type' => 'default'];

        $form->populate($defaultdata, false);
        $form->populate($userdata);

        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isChanged());
        $this->assertEquals(['Form input field Type is not valid.'], $form->getErrors());
        $form->commit();
        $this->assertFalse($form->isChanged());
        $this->assertEquals($expecteddata, $form->toArray());
        unset($expecteddata['save']);
        $this->assertEquals($expecteddata, $form->getData(true));
    }

    protected function setUp(): void
    {
        $_SESSION['csrf_token'] = $this->old_csrf;
    }
}
