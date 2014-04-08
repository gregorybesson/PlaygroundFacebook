<?php

namespace PlaygroundFacebookTest\Form;

use PlaygroundFacebookTest\Bootstrap;

class AppTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $form;

    protected $formData;

    public function setUp()
    {
        // Set fake data
        $this->formData = array(
                'id'        => '0',
                'appId'     => '111222333444555',
                'appName'   => 'test',
                'appSecret' => 'absh56yh2d4zd196hsy6hy54gqt99k2s',
        );

        parent::setUp();
    }

    public function getForm()
    {
        if (null === $this->form) {
            $sm = Bootstrap::getServiceManager();
            $this->form = $sm->get('playgroundfacebook_app_form');
        }

        return $this->form;
    }

    public function testIsFormValidWithData()
    {

        $this->getForm()->setData($this->formData);
        $this->assertTrue($this->getForm()->isValid());

    }

    public function testIsFormNotValidWithoutRequiredData()
    {
        $invalid_data = $this->formData;
        unset($invalid_data['id']);

        $this->getForm()->setData($invalid_data);
        $this->assertFalse($this->getForm()->isValid());

    }

    public function testIsFormNotValidWithoutData()
    {

        $this->getForm()->setData(array());
        $this->assertFalse($this->getForm()->isValid());

    }

}
