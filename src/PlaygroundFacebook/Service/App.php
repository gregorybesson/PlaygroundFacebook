<?php

namespace PlaygroundFacebook\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use Zend\Stdlib\Hydrator\ClassMethods;
use PlaygroundFacebook\Options\ModuleOptions;

class App extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var AppMapperInterface
     */
    protected $appMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var AppServiceOptionsInterface
     */
    protected $options;

    public function create(array $data, $app)
    {
        $form  = $this->getServiceManager()->get('playgroundfacebook_app_form');
        $form->setHydrator(new ClassMethods());
        $form->bind($app);
        $form->setData($data);

        $facebook = new \Facebook(array(
                'appId' => $data['appId'],
                'secret' => $data['appSecret'],
                'cookie' => false,
        ));
        $accessToken = $facebook->getAppId() . '|' . $facebook->getApiSecret();
        try {
            $app_details = $facebook->api('/'.$facebook->getAppId(), 'GET',
                array(
                    'access_token' => $accessToken
                )
            );
        } catch (\FacebookApiException $e) {
            $form->get('appId')->setMessages(array('Cette application n\'existe pas ou le secret key est incorrect'));

            return false;
        }

        if (!$form->isValid()) {
            return false;
        }

       return $this->getAppMapper()->insert($app);
    }

    public function edit(array $data, $app)
    {
        $form  = $this->getServiceManager()->get('playgroundfacebook_app_form');
        $form->setHydrator(new ClassMethods());
        $form->bind($app);
        $form->setData($data);

        $facebook = new \Facebook(array(
                'appId' => $data['appId'],
                'secret' => $data['appSecret'],
                'cookie' => false,
        ));
        $accessToken = $facebook->getAppId() . '|' . $facebook->getApiSecret();
        try {
            $app_details = $facebook->api('/'.$facebook->getAppId(), 'GET',
                    array(
                            'access_token' => $accessToken
                    )
            );
        } catch (\FacebookApiException $e) {
            $form->get('appId')->setMessages(array('Cette application n\'existe pas ou le secret key est incorrect'));

            return false;
        }

        if (!$form->isValid()) {
            return false;
        }

        return$this->getAppMapper()->update($app);

    }

    public function remove($app)
    {
        return $this->getAppMapper()->remove($app);

    }

    /**
     * getActiveApps
     *
     * @return Array of PlaygroundFacebook\Entity\App
     */
    public function getAvailableApps()
    {
        $em = $this->getServiceManager()->get('playgroundfacebook_doctrine_em');

        $query = $em->createQuery('SELECT f FROM PlaygroundFacebook\Entity\App f WHERE f.isAvailable = true ORDER BY f.updatedAt DESC');
        $apps = $query->getResult();

        return $apps;
    }

    /**
     * getAppMapper
     *
     * @return AppMapperInterface
     */
    public function getAppMapper()
    {
        if (null === $this->appMapper) {
            $this->appMapper = $this->getServiceManager()->get('playgroundfacebook_app_mapper');
        }

        return $this->appMapper;
    }

    /**
     * setAppMapper
     *
     * @param  AppMapperInterface $appMapper
     * @return App
     */
    public function setAppMapper(AppMapperInterface $appMapper)
    {
        $this->appMapper = $appMapper;

        return $this;
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgroundfacebook_module_options'));
        }

        return $this->options;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $locator
     * @return Action
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
