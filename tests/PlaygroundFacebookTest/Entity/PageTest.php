<?php

namespace PlaygroundFacebookTest\Entity;

use PlaygroundFacebookTest\Bootstrap;
use \PlaygroundFacebook\Entity\Page as PageEntity;

class PageTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $pageData;

    public function setUp()
    {

         // Set fake data
        $this->pageData = array(
                'pageId'     => '111222333444555',
                'pageName' => 'MyPage',
                );

        parent::setUp();
    }


    public function testGetPageId()
    {

        $app = new PageEntity();
        $app->populate($this->pageData);

        $this->assertEquals($this->pageData['pageId'], $app->getPageId());

    }


    public function testGetPageName()
    {

        $app = new PageEntity();
        $app->populate($this->pageData);

        $this->assertEquals($this->pageData['pageName'], $app->getPageName());

    }


}