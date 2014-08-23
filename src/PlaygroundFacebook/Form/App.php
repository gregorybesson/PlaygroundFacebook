<?php

namespace PlaygroundFacebook\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Mvc\I18n\Translator;
use ZfcBase\Form\ProvidesEventsForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\ServiceManager;

class App extends ProvidesEventsForm
{

    protected $serviceManager;

    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        // The form will hydrate an object collection of type "Page"
        // This is the secret for working with collections with Doctrine
        // (+ add'Collection'() and remove'Collection'() and "cascade" in corresponding Entity
        // https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md

        //         $this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundFacebook\Entity\App'));
        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundFacebook\Entity\App');
        $hydrator->addStrategy('action', new \PlaygroundCore\Stdlib\Hydrator\Strategy\ObjectStrategy());
        $this->setHydrator($hydrator);

        $this->add(array(
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0
            )
        ));

        $this->add(array(
            'name' => 'appId',
            'options' => array(
                'label' => $translator->translate('Facebook app_id', 'playgroundfacebook'),
            ),
        ));

        $this->add(array(
                'name' => 'appName',
                'options' => array(
                        'label' => $translator->translate('Facebook app name', 'playgroundfacebook'),
                ),
                'attributes' => array(
                        'value' => '',
                        'readonly' => 'readonly'
                ),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'appIdRetrieved',
                'attributes' =>  array(
                    'id' => 'appIdRetrieved',
                ),
                'options' => array(
                        'label' => $translator->translate('Facebook app_id retrieved', 'playgroundfacebook'),
                        'empty_option' => $translator->translate('Choose from the list below', 'playgroundfacebook'),
                ),
        ));

        $this->add(array(
            'name' => 'appSecret',
            'options' => array(
                'label' => $translator->translate('Facebook app_secret', 'playgroundfacebook'),
            ),
        ));

        $this->add(array(
                'name' => 'pages',
                'type' => 'DoctrineModule\Form\Element\ObjectMultiCheckbox',
                'options' => array(
                        //'empty_option' => $translator->translate('Select an app', 'playgroundfacebook'),
                        'label' => $translator->translate('Pages', 'playgroundfacebook'),
                        'object_manager' => $entityManager,
                        'target_class' => 'PlaygroundFacebook\Entity\Page',
                        'property' => 'pageName'
                ),
                'attributes' => array(
                        'required' => false,
                        //'multiple' => 'multiple',
                )
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
