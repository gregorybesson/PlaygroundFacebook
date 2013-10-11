<?php

namespace PlaygroundFacebookTest\Mapper;

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

    protected $appData;

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
        $this->appData = array(
                'appId'     => '111222333444555',
                'appSecret' => 'absh56yh2d4zd196hsy6hy54gqt99k2s',
                );

        parent::setUp();
    }

    public function tearDown()
    {
        $dbh = $this->em->getConnection();

        // Clean up
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }

    public function getAppMapper()
    {
        if (null === $this->appMapper) {
            $sm = Bootstrap::getServiceManager();
            $this->appMapper = $sm->get('playgroundfacebook_app_mapper');
        }

        return $this->appMapper;
    }

    public function testCanInsertNewRecord()
    {
        // Set an entity with fake data
        $app = new AppEntity();
        $app->populate($this->appData);

        // Test insert
        $app = $this->getAppMapper()->insert($app);
        $this->assertEquals($this->appData['appId'], $app->getAppId());

        // Return inserted id
        return $app->getId();
    }

    /**
     * @depends testCanInsertNewRecord
     */
    public function testCanUpdateInsertedRecord($id)
    {
        // Set a data to update
        $updated_field = '555444333222111';

        // Get inserted entity
        $app = $this->em->getRepository('PlaygroundFacebook\Entity\App')->find($id);
        $this->assertInstanceOf('PlaygroundFacebook\Entity\App', $app);
        $this->assertEquals($this->appData['appId'], $app->getAppId());

        // Test update
        $app->setAppId($updated_field);
        $app = $this->getAppMapper()->update($app);
        $this->assertEquals($updated_field, $app->getAppId());

    }

    /**
     * @depends testCanInsertNewRecord
     */
    public function testCanFindInsertedRecordById($id)
    {
        // Get inserted entity
        $app = $this->em->getRepository('PlaygroundFacebook\Entity\App')->find($id);
        $this->assertInstanceOf('PlaygroundFacebook\Entity\App', $app);

        // Test find by id
        $app_found = $this->getAppMapper()->findById($app->getId());
        $this->assertEquals("object", gettype($app_found));
        $this->assertEquals("PlaygroundFacebook\Entity\App", get_class($app_found));
        $this->assertEquals($app->getId(), $app_found->getId());

    }

//     /**
//      * @depends testCanInsertNewRecord
//      */
//     public function testCanFindInsertedRecordByAppId($id)
//     {
//         // Get inserted entity
//         $app = $this->em->getRepository('PlaygroundFacebook\Entity\App')->find($id);
//         $this->assertInstanceOf('PlaygroundFacebook\Entity\App', $app);

//         // Test find by app id
//         $app_found = $this->getAppMapper()->findByAppId($app->getAppId());
//         $this->assertEquals("object", gettype($app_found));
//         $this->assertEquals("PlaygroundFacebook\Entity\App", get_class($app_found));
//         $this->assertEquals($app->getAppId(), $app_found->getAppId());

//     }

    /**
     * @depends testCanInsertNewRecord
     */
    public function testCanRemoveInsertedRecord($id)
    {
        // Get inserted entity
        $app = $this->em->getRepository('PlaygroundFacebook\Entity\App')->find($id);
        $this->assertInstanceOf('PlaygroundFacebook\Entity\App', $app);

        // Test remove
        $this->getAppMapper()->remove($app);
        $app = $this->em->getRepository('PlaygroundFacebook\Entity\App')->find($id);
        $this->assertEquals($app, null);
    }



}