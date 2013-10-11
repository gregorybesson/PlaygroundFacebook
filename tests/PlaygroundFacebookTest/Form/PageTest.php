<?php

namespace PlaygroundFacebookTest\Form;

use PlaygroundFacebookTest\Bootstrap;

class PageTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $form;

    protected $formData;

    public function setUp()
    {
        // Set fake data
        $this->formData = array(
                'id'        => '0',
                'pageIdRetrieved' => '555444333111',
        );

        parent::setUp();
    }

    public function getForm()
    {
        if (null === $this->form) {
            $sm = Bootstrap::getServiceManager();
            $this->form = $sm->get('playgroundfacebook_page_form');
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
