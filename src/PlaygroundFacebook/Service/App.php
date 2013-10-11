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
     *
     * @var AppMapperInterface
     */
    protected $appMapper;

    /**
     *
     * @var AppPageMapperInterface
     */
    protected $appPageMapper;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     *
     * @var AppServiceOptionsInterface
     */
    protected $options;

    public function create(array $data, $app)
    {

        // App ID chosen from the retrieved app list is prior to app id entered by the user
        $appId = $data['appId'];
        $appIdField = 'appId';
        if (isset($data['appIdRetrieved']) && $data['appIdRetrieved']) {
            $appId = $data['appIdRetrieved'];
            $data['appId'] = $appId;
            $appIdField = 'appIdRetrieved';
        }

        $form = $this->getServiceManager()->get('playgroundfacebook_app_form');
        $form->setHydrator(new ClassMethods());
        $form->bind($app);
        $form->setData($data);

        $facebook = new \Facebook(array(
            'appId' => $appId,
            'secret' => $data['appSecret'],
            'cookie' => false
        ));
        $accessToken = $facebook->getAppId() . '|' . $facebook->getApiSecret();
        try {
            $app_details = $facebook->api('/' . $facebook->getAppId(), 'GET', array(
                'access_token' => $accessToken
            ));
        } catch (\FacebookApiException $e) {
            $form->get($appIdField)->setMessages(array(
                'Cette application n\'existe pas ou le secret key est incorrect'
            ));

            return false;
        }

        if (! $form->isValid()) {
            return false;
        }

        return $this->getAppMapper()->insert($app);
    }

    public function edit(array $data, $app)
    {
        $form = $this->getServiceManager()->get('playgroundfacebook_app_form');
        $form->setHydrator(new ClassMethods());
        $form->bind($app);
        $form->setData($data);

        $facebook = new \Facebook(array(
            'appId' => $data['appId'],
            'secret' => $data['appSecret'],
            'cookie' => false
        ));
        $accessToken = $facebook->getAppId() . '|' . $facebook->getApiSecret();
        try {
            $app_details = $facebook->api('/' . $facebook->getAppId(), 'GET', array(
                'access_token' => $accessToken
            ));
        } catch (\FacebookApiException $e) {
            $form->get('appId')->setMessages(array(
                'Cette application n\'existe pas ou le secret key est incorrect'
            ));

            return false;
        }

        if (! $form->isValid()) {
            return false;
        }

        return $this->getAppMapper()->update($app);
    }

    public function remove($app)
    {
        return $this->getAppMapper()->remove($app);
    }

    /**
     * getPagesForApp
     *
     * @return Array of PlaygroundFacebook\Entity\AppPage
     */
    public function getPagesForApp($app)
    {
        if ($app){
            $id_app = $app->getId();
            if ($id_app){
                return $this->getAppPageMapper()->findBy(array('idApp' => $id_app));
            }
        }

        return false;

    }

    /**
     * getAvailableApps
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
     * getAppsFromFacebookAccount
     *
     * @return Array
     */
    public function getAppsFromFacebookAccount(array $fb_config_data)
    {
        $registeredFbApps = array();
        $returnedFbApps = array();

        // Get all Facebook apps already persisted into Playground

        $pgApps = $this->getAppMapper()->findAll();
        foreach ($pgApps as $app) {
            $registeredFbApps[$app->getAppId()] = true;
        }

        // Retrieve user Facebook identifier, if user is connected to Facebook

        $user = null;

        $facebookPtf = new \Facebook(array(
            'appId' => $fb_config_data['fb_appid'],
            'secret' => $fb_config_data['fb_secret'],
            'cookie' => false
        ));

        $user = $facebookPtf->getUser();

        // Try to retrieve apps from Facebook, if user is connected to Facebook

        if ($user) {

            try {

                // Retrieve apps administred by the user, if the Facebook account is a developer account

                $userFbApps = $facebookPtf->api('/me/applications/developer', 'GET');

                if (isset($userFbApps['data']) && is_array($userFbApps['data'])) {
                    foreach ($userFbApps['data'] as $userFbApp) {
                        if (! array_key_exists($userFbApp['id'], $registeredFbApps)) { // ignore App ID already registered in Playground
                            $returnedFbApps['apps_from_developer'][] = $userFbApp;
                        } else {
                            $registeredFbApps[$userFbApp['id']] = true; // mark App ID as seen, in order to show it only one time
                        }
                    }
                }

                // Retrieve apps from pages administred by the user (apps that are into page tabs)

                $userFbPages = $facebookPtf->api('/me/accounts', 'GET');

                if (isset($userFbPages['data']) && is_array($userFbPages['data'])) {
                    foreach ($userFbPages['data'] as $userFbPage) {
                        if (array_key_exists('id', $userFbPage)) {
                            $userFbPageId = $userFbPage['id'];
                            $userFbPageName = $userFbPage['name'];
                            $userFbTabs = $facebookPtf->api('/' . $userFbPageId . '/tabs', 'GET');

                            if (isset($userFbTabs['data']) && is_array($userFbTabs['data'])) {
                                foreach ($userFbTabs['data'] as $userFbTab) {
                                    if (array_key_exists('application', $userFbTab)) {
                                        $userFbApp = $userFbTab['application'];
                                        if (! array_key_exists($userFbApp['id'], $registeredFbApps)) { // ignore App ID already registered in Playground
                                            $userFbApp['page_id'] = $userFbPageId;
                                            $userFbApp['page_name'] = $userFbPageName;
                                            $returnedFbApps['apps_from_pages'][] = $userFbApp;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (FacebookApiException $e) {}
        }

        return $returnedFbApps;
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
     * @param AppMapperInterface $appMapper
     * @return App
     */
    public function setAppMapper(AppMapperInterface $appMapper)
    {
        $this->appMapper = $appMapper;

        return $this;
    }

    /**
     * setAppPageMapper
     *
     * @param AppMapperInterface $appMapper
     * @return App
     */
    public function setAppPageMapper(AppPageMapperInterface $appPageMapper)
    {
        $this->appPageMapper = $appPageMapper;

        return $this;
    }

    /**
     * getAppPageMapper
     *
     * @return AppPageMapperInterface
     */
    public function getAppPageMapper()
    {
        if (null === $this->appPageMapper) {
            $this->appPageMapper = $this->getServiceManager()->get('playgroundfacebook_app_page_mapper');
        }

        return $this->appPageMapper;
    }



    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        if (! $this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()
                ->get('playgroundfacebook_module_options'));
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
     * @param ServiceManager $locator
     * @return Action
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
