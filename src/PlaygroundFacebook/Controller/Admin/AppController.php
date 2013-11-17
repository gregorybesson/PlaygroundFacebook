<?php

namespace PlaygroundFacebook\Controller\Admin; // PlaygroundFacebook\Controller\App;

use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundFacebook\Options\ModuleOptions;
use Zend\View\Model\ViewModel;
use Zend\Stdlib\Hydrator\ClassMethods;

class AppController extends AbstractActionController
{
    protected $options, $appMapper, $pageMapper, $appPageMapper, $adminAppService, $adminPageService;

    public function listAction()
    {
        $config = $this->getAdminAppService()->getServiceManager()->get('config');
        if (isset($config['facebook'])) {
            $platformFbAppId     = $config['facebook']['fb_appid'];
            $platformFbAppSecret = $config['facebook']['fb_secret'];
            $fbPage              = $config['facebook']['fb_page'];
        }
        $appMapper   = $this->getAppMapper();
        $apps        = $appMapper->findAll();
        $FBApps      = array();
        $fbLogged    = false;
        $fbAllowed   = false;
        $fbLoginUrl  = '';
        $fbPages     = $this->getPageMapper()->findAll();

        $user = null;
        // Create our Application instance with the FB App associated with the plateform
        $facebookPtf = new \Facebook(array(
                'appId' => $platformFbAppId,
                'secret' => $platformFbAppSecret,
                'cookie' => false,
        ));

        $user = $facebookPtf->getUser();


        if ($user) {
            try {

                $userFbAccounts = $facebookPtf->api('/me/accounts', 'GET');

                $userFbApps = $facebookPtf->api('/me/applications/developer', 'GET');

                $user_profile =  $facebookPtf->api('/me','GET');
                $fbLogged = true;

                $userAccessToken = $facebookPtf->getAccessToken();
                $pageAccessToken = $facebookPtf->api('/'.$fbPage.'?fields=access_token', 'GET');

            } catch (\FacebookApiException $e) {

                $fbLoginUrl = $facebookPtf->getLoginUrl(array('scope' => 'manage_pages'));
                //error_log($e->getType());
                //error_log($e->getMessage());
            }

        } else {
            $fbLoginUrl = $facebookPtf->getLoginUrl(array('scope' => 'manage_pages'));
        }


        foreach ($apps as $app) {
            $line = array();
            // non authentifié, je n'accède pas aux apps en Sandbox...
            $facebook = new \Facebook(array(
                'appId' => $app->getAppId(),
                'secret' => $app->getAppSecret(),
                'cookie' => false,
            ));

            // WtF ??? getApplicationAccessToken is protected :( have to build the token by myself !
            $accessToken = $facebook->getAppId() . '|' . $facebook->getApiSecret();

            // Application details available to all (Don't have to be logged to Facebook as Admin)
            try {
                $app_details = $facebook->api('/'.$app->getAppId(), 'GET',
                    array(
                        'access_token' => $accessToken
                    )
                );

                // Persist the app name into the database
                if (isset($app_details['name'])){
                    $app->setAppName($app_details['name']);
                    $appMapper->update($app);
                }

            } catch (\FacebookApiException $e) {
                $app_details['name'] = 'N/A';
                $app_details['id'] = 'Cette application n\'existe plus. Vous devriez la supprimer';
                $app_details['custom_name'] = '';
                $app_details['logo'] = '';
                $app_details['link'] = $this->url()->fromRoute('admin/facebook/app/remove', array('appId' => $app->getId()));

            }

            // Get pages linked to the app

            $app_details['has_pages'] = false;
            $app_details['pages_number'] = '';

            $pages = $app->getPages()->toArray();
            if (is_array($pages) && sizeof($pages)){
                $app_details['has_pages'] = true;
                $app_details['pages_number'] = sizeof($pages);
            }

            // Get informations from the Page. Logged user has to be admin of the page.
            if ($user) {
                if (isset($pageAccessToken['access_token'])) {
                    $fbAllowed = true;
                    // Check that the app is correctly installed on the page
                    $tabExist = $facebook->api('/'.$fbPage.'/tabs/'.$app->getAppId(), 'GET', array(
                        'access_token'=> $pageAccessToken['access_token']
                    ));

                    if ($tabExist['data']) {
                        $app->setIsInstalled(true);
                        $app_details['is_installed']     = true;
                        $app_details['link']             = $tabExist['data'][0]['link'];
                        $app_details['position']         = $tabExist['data'][0]['position'];
                        $app_details['custom_name']      = isset($tabExist['data'][0]['custom_name'])?$tabExist['data'][0]['custom_name']:'';
                        $app_details['custom_image_url'] = isset($tabExist['data'][0]['custom_image_url'])?$tabExist['data'][0]['custom_image_url']:'';
                    } else {
                        $app->setIsInstalled(false);
                        $app_details['is_installed']        = false;
                    }
                    $appMapper->update($app);

                } else {
                    // Not enough right on the page admin
                    $fbAllowed = false;
                }

            } else {
                $app_details['logged']  = false;
            }

            // Informations on the Facebook Tab App Container
            $app_details['source_type']  = $app->getPageTabSourceType();
            $app_details['source_title'] = $app->getPageTabSourceTitle();
            $app_details['source_id']    = $app->getPageTabSourceId();
            $app_details['is_available'] = $app->getIsAvailable();
            $app_details['object_id']    = $app->getId();

            $FBApps[$app->getAppId()]    = $app_details;
        }

        if (is_array($FBApps)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($FBApps));
            $paginator->setItemCountPerPage(10);
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $FBApps;
        }

        return array(
            'apps'      => $paginator,
            'fbLogged'  => $fbLogged,
            'fbAllowed' => $fbAllowed,
            'fbLoginUrl'=> $fbLoginUrl
        );
    }

    public function createAction()
    {
        $form = $this->getServiceLocator()->get('playgroundfacebook_app_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/facebook/app/create', array('appId' => 0)));
        $form->setAttribute('method', 'post');

        // Get the apps administered by the Facebook account (if admin user is connected to Facebook)

        $config = $this->getAdminAppService()->getServiceManager()->get('config');
        $appsFromFacebook = array();
        if (isset($config['facebook'])) {
            $appsFromFacebook = $this->getAdminAppService()->getAppsFromFacebookAccount($config['facebook']);
        }

        // Construct the select box options for app selection

        $userFbAppsOptions = array();

        if (isset($appsFromFacebook['apps_from_developer']) && sizeof($appsFromFacebook['apps_from_developer'])){
            $tmpApps = array();
            foreach ($appsFromFacebook['apps_from_developer'] as $app){
                $tmpApps[$app['id']] = $app['id'] . ' - ' . $app['name'];
            }
            $userFbAppsOptions['apps_from_developper'] = array(
                'label' => 'Applications (developer)',
                'options' => $tmpApps
            );
        }
        if (isset($appsFromFacebook['apps_from_pages']) && sizeof($appsFromFacebook['apps_from_pages'])){
            $tmpApps = array();
            $previousPageId = $appsFromFacebook['apps_from_pages'][0]['page_id'];
            $previousPageName = $appsFromFacebook['apps_from_pages'][0]['page_name'];
            $isAdded = false;
            foreach ($appsFromFacebook['apps_from_pages'] as $app){

                if(isset($app['id'])){
                    if ($previousPageId != $app['page_id']){
                        $userFbAppsOptions['page_'.$previousPageId] = array(
                                'label' => 'Page ' . $previousPageName,
                                'options' => $tmpApps
                        );
                        $previousPageId = $app['id'];
                        $previousPageName = $app['name'];
                        $tmpApps = array();
                        $isAdded = true;
                    }
                    $tmpApps[$app['id']] = $app['id'] . ' - ' . $app['name'];
                }
            }
            if (!$isAdded){
                $userFbAppsOptions['page_'.$app['id']] = array(
                        'label' => 'Page ' . $app['name'],
                        'options' => $tmpApps
                );
            }
        }

        if (sizeof($userFbAppsOptions)){
            $form->get('appIdRetrieved')->setValueOptions($userFbAppsOptions);
        }


        $app = new \PlaygroundFacebook\Entity\App();
        $form->bind($app);

        // Persist app into Playground

        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $app = $this->getAdminAppService()->create($data, $app);
            if ($app) {
                $this->flashMessenger()->setNamespace('playgroundfacebook')->addMessage('L\'appli FB a été créée');

                return $this->redirect()->toRoute('admin/facebook/app/list');
            }
        }

        // Render creation form

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-facebook/app/edit');

        return $viewModel->setVariables(array('form' => $form, 'isCreate' => true));
    }

    public function editAction()
    {
        $appId = $this->getEvent()->getRouteMatch()->getParam('appId');
        $app = $this->getAppMapper()->findById($appId);
        $form = $this->getServiceLocator()->get('playgroundfacebook_app_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/facebook/app/edit', array('appId' => $appId)));
        $form->setAttribute('method', 'post');

        $form->bind($app);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();

            $app = $this->getAdminAppService()->edit($data, $app);
            if ($app) {
                $this->flashMessenger()->setNamespace('playgroundfacebook')->addMessage('L\' Appli a été créée');

                return $this->redirect()->toRoute('admin/facebook/app/list');
            }
        }

        return array('form' => $form, 'isCreate' => false);
    }

    public function removeAction()
    {
        $appId = $this->getEvent()->getRouteMatch()->getParam('appId');
        $app = $this->getAppMapper()->findById($appId);
        if ($app) {
            $this->getAdminAppService()->remove($app);
            $this->flashMessenger()->setNamespace('playgroundfacebook')->addMessage('Appli supprimée');
        }

        return $this->redirect()->toRoute('admin/facebook/app/list');
    }


    public function installAction()
    {
        $config = $this->getAdminAppService()->getServiceManager()->get('config');
        if (isset($config['facebook'])) {
            $platformFbAppId     = $config['facebook']['fb_appid'];
            $platformFbAppSecret = $config['facebook']['fb_secret'];
//             $fbPage              = $config['facebook']['fb_page'];
        }

        $appId = $this->getEvent()->getRouteMatch()->getParam('appId');
        $app = $this->getAppMapper()->findById($appId);


        $user = null;
         // Create our Application instance with the FB App associated with the plateform
        $facebook = new \Facebook(array(
                'appId' => $platformFbAppId,
                'secret' => $platformFbAppSecret,
                'cookie' => true,
                'fileUpload' => true,
        ));

        $user = $facebook->getUser();

        if ($user) {

            $userAccessToken = $facebook->getAccessToken();

            // For each FB page associated to the app :

            $pages = $app->getPages()->toArray();

            if (is_array($pages) && sizeof($pages)){
                foreach ($pages as $page){

                    $fbPage = $page->getPageId();

                    $pageAccessToken = $facebook->api('/'.$fbPage.'?fields=access_token', 'GET');

                    // Check that the app is correctly installed on the page
                    $tabExist = $facebook->api('/'.$fbPage.'/tabs/'.$app->getAppId(), 'GET', array(
                            'access_token'=> $pageAccessToken['access_token']
                    ));

                    if (!$tabExist['data']) {
                        // Install the Page Tab App on a page
                        $install = $facebook->api('/'.$fbPage.'/tabs', 'POST', array(
                                'app_id'=> $app->getAppId(),
                                'access_token'=> $pageAccessToken['access_token']
                        ));

                        // Check that the app is correctly installed on the page
                        $tabExist = $facebook->api('/'.$fbPage.'/tabs/'.$app->getAppId(), 'GET', array(
                                'access_token'=> $pageAccessToken['access_token']
                        ));
                    }
                    //it contains the whole path to the tab_id including the page_id...
                    $tabId = $tabExist['data'][0]['id'];

                    // update with the tab title and tab image
                    // TEMP: code desactivated because generating curl error of type CURLE_BAD_FUNCTION_ARGUMENT (43)
                    //       see http://curl.haxx.se/libcurl/c/libcurl-errors.html
//                     $update = $facebook->api('/'.$tabId , 'POST', array(
//                             'custom_name'     => $app->getPageTabTitle(),
//                             // wtf : last comment https://developers.facebook.com/bugs/255313014574414/
//                             //'custom_image_url'=> $app->getPageTabImage(),
//                             'custom_image'    => '@' . 'public' . DIRECTORY_SEPARATOR . $app->getPageTabImage(),
//                             'access_token'    => $pageAccessToken['access_token']
//                     ));

//                     // Check that the app is correctly installed on the page
//                     $tabExist = $facebook->api('/'.$fbPage.'/tabs/'.$app->getAppId(), 'GET', array(
//                             'access_token'=> $pageAccessToken['access_token']
//                     ));

                }
            }

            $app->setIsInstalled(true);
            $this->getAppMapper()->update($app);

        } else {
            $loginUrl = $facebook->getLoginUrl(
                array(
                    'scope' => 'manage_pages'
                )
            );

            return $this->redirect()->toUrl($loginUrl);
        }

        return $this->redirect()->toRoute('admin/facebook/app/list');
    }

    public function uninstallAction()
    {
        $config = $this->getAdminAppService()->getServiceManager()->get('config');
        if (isset($config['facebook'])) {
            $platformFbAppId     = $config['facebook']['fb_appid'];
            $platformFbAppSecret = $config['facebook']['fb_secret'];
//             $fbPage              = $config['facebook']['fb_page'];
        }

        $appId = $this->getEvent()->getRouteMatch()->getParam('appId');
        $app = $this->getAppMapper()->findById($appId);

        $user = null;
        // Create our Application instance with the FB App associated with the plateform
        $facebook = new \Facebook(array(
                'appId' => $platformFbAppId,
                'secret' => $platformFbAppSecret,
                'cookie' => true,
                'fileUpload' => true,
        ));

        $user = $facebook->getUser();

        if ($user) {

            $userAccessToken = $facebook->getAccessToken();

            // For each FB page associated to the app :

            $pages = $app->getPages()->toArray();

            if (is_array($pages) && sizeof($pages)){
                foreach ($pages as $page){

                    $fbPage = $page->getPageId();

                    $pageAccessToken = $facebook->api('/'.$fbPage.'?fields=access_token', 'GET');

                    // Check that the app is correctly installed on the page
                    $tabExist = $facebook->api('/'.$fbPage.'/tabs/'.$app->getAppId(), 'GET', array(
                            'access_token'=> $pageAccessToken['access_token']
                    ));
                    //it contains the whole path to the tab_id including the page_id...
                    $tabId = $tabExist['data'][0]['id'];

                    if ($tabExist['data']) {
                        $delete = $facebook->api('/'.$tabId, 'DELETE', array(
                                'access_token'=> $pageAccessToken['access_token']
                        ));
                    }
                }
            }

            $app->setIsInstalled(false);
            $this->getAppMapper()->update($app);

        } else {
            $loginUrl = $facebook->getLoginUrl(
                    array(
                            'scope' => 'manage_pages'
                    )
            );

            return $this->redirect()->toUrl($loginUrl);
        }

        return $this->redirect()->toRoute('admin/facebook/app/list');
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceLocator()->get('playgroundfacebook_module_options'));
        }

        return $this->options;
    }

    public function getAppMapper()
    {
        if (null === $this->appMapper) {
            $this->appMapper = $this->getServiceLocator()->get('playgroundfacebook_app_mapper');
        }

        return $this->appMapper;
    }

    public function setAppMapper(AppMapperInterface $appMapper)
    {
        $this->appMapper = $appMapper;

        return $this;
    }

    public function getPageMapper()
    {
        if (null === $this->pageMapper) {
            $this->pageMapper = $this->getServiceLocator()->get('playgroundfacebook_page_mapper');
        }

        return $this->pageMapper;
    }

    public function setPageMapper(PageMapperInterface $pageMapper)
    {
        $this->pageMapper = $pageMapper;

        return $this;
    }

    public function getAdminAppService()
    {
        if (null === $this->adminAppService) {
            $this->adminAppService = $this->getServiceLocator()->get('playgroundfacebook_app_service');
        }

        return $this->adminAppService;
    }

    public function setAdminAppService($service)
    {
        $this->adminAppService = $service;

        return $this;
    }

    public function getAdminPageService()
    {
        if (null === $this->adminPageService) {
            $this->adminPageService = $this->getServiceLocator()->get('playgroundfacebook_page_service');
        }

        return $this->adminPageService;
    }

    public function setAdminPageService($service)
    {
        $this->adminPageService = $service;

        return $this;
    }
}
