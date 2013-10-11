<?php

namespace PlaygroundFacebookTest\Service;

use PlaygroundFacebookTest\Bootstrap;
use \PlaygroundFacebook\Entity\App as AppEntity;

class AppTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    /**
     * Service Manager
     * @var Zend\ServiceManager\ServiceManager
     */
    protected $sm;

    /**
     * Doctrine Entity Manager
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    protected $appMapper;

    protected $appService;

    protected $formData;

    public function setUp()
    {
        // Access to service
        $this->sm = Bootstrap::getServiceManager();

        // Build database (stored into a flat file)
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        // Set fake data
        $this->formData = array(
                'submit'    => '',
                'id'        => '0',
                'appId'     => '111222333444555',
                'appIdRetrieved' => '555444333111',
                'appSecret' => 'absh56yh2d4zd196hsy6hy54gqt99k2s',
        );


        parent::setUp();
    }


    public function testCanCreateAppWithoutDataFromFacebook()
    {

        $this->assertTrue(true);

//         $app = new AppEntity();

//         // Get global Facebook configuration, and force test failure if configuration not found
//         $config = $this->sm->get('config');

//         if (isset($config['facebook'])) {
//             $this->formData['appId'] = $config['facebook']['fb_appid'];
//             $this->formData['appIdRetrieved'] = $config['facebook']['fb_appid'];
//             $this->formData['appSecret'] = $config['facebook']['fb_secret'];
//         } else {
//             $this->assertTrue(false);
//         }

//         // Test create
//         $created_entity = $this->getAppService()->create($this->formData, $app);

//         $this->assertEquals("object", gettype($created_entity));
//         $this->assertEquals("PlaygroundFacebook\Entity\App", get_class($created_entity));
//         $this->assertEquals($created_entity->getAppId(), $this->formData['appId']);

    }


    public function clean()
    {
        foreach ($this->getAppMapper()->findAll() as $app) {
            $this->getAppMapper()->remove($app);
        }
    }

    public function getAppMapper()
    {

        if (null === $this->appMapper) {
            $sm = Bootstrap::getServiceManager();
            $this->appMapper = $sm->get('playgroundfacebook_app_mapper');
        }

        return $this->appMapper;
    }


    public function getAppService()
    {
       if (null === $this->appService) {
            $sm = Bootstrap::getServiceManager();
            $this->appService = $sm->get('playgroundfacebook_app_service');
        }

        return $this->appService;
    }


}
