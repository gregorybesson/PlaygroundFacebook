<?php

namespace PlaygroundFacebookTest\Entity;

use PlaygroundFacebookTest\Bootstrap;
use \PlaygroundFacebook\Entity\App as AppEntity;

class AppTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $appData;

    public function setUp()
    {

        // Set fake data
        $this->appData = array(
                'appId'     => '111222333444555',
                'appSecret' => 'absh56yh2d4zd196hsy6hy54gqt99k2s',
                );

        parent::setUp();
    }


    public function testGetAppId()
    {

        $app = new AppEntity();
        $app->populate($this->appData);

        $this->assertEquals($this->appData['appId'], $app->getAppId());

    }


    public function testGetAppSecret()
    {

        $app = new AppEntity();
        $app->populate($this->appData);

        $this->assertEquals($this->appData['appSecret'], $app->getAppSecret());

    }


}