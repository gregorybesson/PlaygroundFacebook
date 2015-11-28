<?php

namespace PlaygroundFacebookTest\Mapper;

use PlaygroundFacebookTest\Bootstrap;
use \PlaygroundFacebook\Entity\Page as PageEntity;

class PageTest extends \PHPUnit_Framework_TestCase
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

    protected $pageMapper;

    protected $pageData;

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
        $this->pageData = array(
                'pageId'     => '111222333444555',
                'pageName' => 'MyPage',
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

    public function getPageMapper()
    {
        if (null === $this->pageMapper) {
            $sm = Bootstrap::getServiceManager();
            $this->pageMapper = $sm->get('playgroundfacebook_page_mapper');
        }

        return $this->pageMapper;
    }

    public function testCanInsertNewRecord()
    {
        // Set an entity with fake data
        $page = new PageEntity();
        $page->populate($this->pageData);

        // Test insert
        $page = $this->getPageMapper()->insert($page);
        $this->assertEquals($this->pageData['pageId'], $page->getPageId());

        // Return inserted id
        return $page->getId();
    }

    /**
     * @depends testCanInsertNewRecord
     */
    public function testCanUpdateInsertedRecord($id)
    {
        // Set a data to update
        $updated_field = '555444333222111';

        // Get inserted entity
        $page = $this->em->getRepository('PlaygroundFacebook\Entity\Page')->find($id);
        $this->assertInstanceOf('PlaygroundFacebook\Entity\Page', $page);
        $this->assertEquals($this->pageData['pageId'], $page->getPageId());

        // Test update
        $page->setPageId($updated_field);
        $page = $this->getPageMapper()->update($page);
        $this->assertEquals($updated_field, $page->getPageId());

    }

    /**
     * @depends testCanInsertNewRecord
     */
    public function testCanRemoveInsertedRecord($id)
    {
        // Get inserted entity
        $page = $this->em->getRepository('PlaygroundFacebook\Entity\Page')->find($id);
        $this->assertInstanceOf('PlaygroundFacebook\Entity\Page', $page);

        // Test remove
        $this->getPageMapper()->remove($page);
        $page = $this->em->getRepository('PlaygroundFacebook\Entity\Page')->find($id);
        $this->assertEquals($page, null);
    }
}
