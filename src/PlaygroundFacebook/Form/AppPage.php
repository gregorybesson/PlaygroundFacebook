<?php

namespace PlaygroundFacebook\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;

class AppPage extends ProvidesEventsForm
{
    protected $userEditOptions;
    protected $userEntity;
    protected $serviceManager;

    public function __construct($name = null, Translator $translator)
    {
        parent::__construct($name);

        $this->add(array(
                'name' => 'idApp',
                'type' => 'Zend\Form\Element\Hidden',
                'attributes' => array(
                        'value' => 0
                ),
                'options' => array(
                        'label' => $translator->translate('App name', 'playgroundfacebook')
                ),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'idPage',
                'options' => array(
                        'label' => $translator->translate('Publish into page', 'playgroundfacebook'),
                        'empty_option' => $translator->translate('Choose from the list below', 'playgroundfacebook'),
                ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement
            ->setAttributes(array(
                'type'  => 'submit',
            ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));
    }
}
