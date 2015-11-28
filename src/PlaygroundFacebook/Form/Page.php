<?php

namespace PlaygroundFacebook\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\ServiceManager;

class Page extends ProvidesEventsForm
{
    protected $userEditOptions;
    protected $userEntity;
    protected $serviceManager;

    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        // The form will hydrate an object collection of type "App"
        // This is the secret for working with collections with Doctrine
        // (+ add'Collection'() and remove'Collection'() and "cascade" in corresponding Entity
        // https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md

//         $this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundFacebook\Entity\Page'));
        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundFacebook\Entity\Page');
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
            'name' => 'pageId',
            'options' => array(
                'label' => $translator->translate('Facebook page id', 'playgroundfacebook'),
            ),
            'attributes' => array(
                    'readonly' => 'readonly'
            )
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'pageIdRetrieved',
                'options' => array(
                        'label' => $translator->translate('Facebook page id retrieved', 'playgroundfacebook'),
                        'empty_option' => $translator->translate('Choose from the list below', 'playgroundfacebook'),
                ),
        ));

        $this->add(array(
                'name' => 'pageName',
//                 'type' => 'Zend\Form\Element\Hidden',
                'options' => array(
                        'label' => $translator->translate('Facebook page name', 'playgroundfacebook'),
                ),
                'attributes' => array(
                        'value' => '',
                        'readonly' => 'readonly'
                ),
        ));

        $this->add(array(
                'name' => 'pageLink',
//                 'type' => 'Zend\Form\Element\Hidden',
//                 'attributes' => array(
//                         'value' => ''
//                 )
                'options' => array(
                        'label' => $translator->translate('Facebook page link', 'playgroundfacebook'),
                ),
                'attributes' => array(
                        'value' => '',
                        'readonly' => 'readonly'
                ),
        ));

//         $appFieldset = new AppFieldset(null,$serviceManager,$translator);
//         $this->add(array(
//                 'type'    => 'Zend\Form\Element\Collection',
//                 'name'    => 'apps',
//                 'options' => array(
//                         'id'    => 'apps',
//                         'label' => $translator->translate('List of tab apps', 'playgroundfacebook'),
//                         'count' => 0,
//                         'should_create_template' => true,
//                         'allow_add' => true,
//                         'allow_remove' => true,
//                         'target_element' => $appFieldset
//                 )
//         ));

        $this->add(array(
                'name' => 'apps',
                'type' => 'DoctrineModule\Form\Element\ObjectMultiCheckbox',
                'options' => array(
                        'empty_option' => $translator->translate('Select an app', 'playgroundfacebook'),
                        'label' => $translator->translate('Apps', 'playgroundfacebook'),
                        'object_manager' => $entityManager,
                        'target_class' => 'PlaygroundFacebook\Entity\App',
                        'property' => 'appName'
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

//     /**
//      * Retrieve service manager instance
//      *
//      * @return ServiceManager
//      */
//     public function getServiceManager ()
//     {
//         return $this->serviceManager;
//     }

//     /**
//      * Set service manager instance
//      *
//      * @param  ServiceManager $serviceManager
//      * @return User
//      */
//     public function setServiceManager (ServiceManager $serviceManager)
//     {
//         $this->serviceManager = $serviceManager;

//         return $this;
//     }

//     /**
//      *
//      * @return array
//      */
//     public function getApps ()
//     {
//         $appsArray = array();
//         $appService = $this->getServiceManager()->get('playgroundfacebook_app_service');
//         $apps = $appService->getAppMapper()->findAll();

//         foreach ($apps as $app) {
//             $appsArray[$app->getId()] = $app->getAppName();
//         }

//         return $appsArray;
//     }
}
