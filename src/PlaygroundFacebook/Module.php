<?php

namespace PlaygroundFacebook;

use Zend\Session\Container;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $options = $serviceManager->get('playgroundcore_module_options');
        $translator = $serviceManager->get('translator');
        $locale = $options->getLocale();
        if (!empty($locale)) {
            //translator
            $translator->setLocale($locale);

            // plugins
            $translate = $serviceManager->get('viewhelpermanager')->get('translate');
            $translate->getTranslator()->setLocale($locale);
        }
        AbstractValidator::setDefaultTranslator($translator,'playgroundfacebook');

        // If cron is called, the $e->getRequest()->getPost() produces an error so I protect it with
        // this test
        if ((get_class($e->getRequest()) == 'Zend\Console\Request')) {
            return;
        }

        // If PlaygroundGame is installed, I can add Fb apps to benefit from
        $e->getApplication()
        ->getEventManager()
        ->getSharedManager()
        ->attach('Zend\Mvc\Application', 'getFbAppIds', array(
                $this,
                'populateAppIds'
        ));

        // If PlaygroundGame is installed, I can check that TheFbAppId can be changed
        $e->getApplication()
        ->getEventManager()
        ->getSharedManager()
        ->attach(
            array(
                'PlaygroundGame\Service\Lottery',
                'PlaygroundGame\Service\PostVote',
                'PlaygroundGame\Service\Quiz',
                'PlaygroundGame\Service\InstantWin'
            ),
            'edit.validate',
            array(
                $this,
                'validateApp'
            )
        );

        // If PlaygroundGame I can update the FB apps dynamically
        $e->getApplication()
        ->getEventManager()
        ->getSharedManager()
        ->attach(
            array(
                'PlaygroundGame\Service\Lottery',
                'PlaygroundGame\Service\PostVote',
                'PlaygroundGame\Service\Quiz',
                'PlaygroundGame\Service\InstantWin'
            ),
            array(
                'create',
                'edit.post'
            ),
            array(
                $this,
                'updateApp'
            )
        );
    }

    /**
     * This method get the Fb apps and add them as array to PlaygroundGame form so
     * that there is non adherence between modules...
     * not that satisfied neither
     *
     * @param  EventManager $e
     * @return array
     */
    public function populateAppIds ($e)
    {
        $appsArray = $e->getParam('apps');

        $appService = $e->getTarget()
        ->getServiceManager()
        ->get('playgroundfacebook_app_service');
        $apps = $appService->getAvailableApps();

        foreach ($apps as $app) {
            $app_label = '';
            if ($app->getAppName()){
                $app_label .= $app->getAppName();
            }
            if ($app->getAppId()){
                $app_label .= ' ('.$app->getAppId().')';
            }
            $appsArray[$app->getAppId()] = $app_label;
        }

        return $appsArray;
    }

    /**
     *
     * @param  EventManager $e
     * @return array
     */
    public function validateApp ($e)
    {
        $game = $e->getParam('game');
        $data = $e->getParam('data');

        $appService = $e->getTarget()
        ->getServiceManager()
        ->get('playgroundfacebook_app_service');

        // The game was previously associated with a FB App...
        if ($game->getFbAppId()) {
            // And there is a change...
            if ($data['fbAppId'] != $game->getFbAppId()) {
                //I must check that we can remove the association...
                $app = $appService->getAppMapper()->findByAppId($game->getFbAppId());
                // And it's not possible if the App is installed on a page...
                if ($app && $app->getIsInstalled()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function updateApp ($e)
    {
        $game       = $e->getParam('game');
        $appService = $e->getTarget()->getServiceManager()->get('playgroundfacebook_app_service');
        $config     = $e->getTarget()->getServiceManager()->get('config');
        $fbDomain   = false;
        if (isset($config['channel']) && isset($config['channel']['facebook']) ) {
            $fbDomain = $config['channel']['facebook'];
        }

        // I create or update the facebook association with the game
        if ($game->getFbAppId()) {

            $urlHelper = $e->getTarget()->getServiceManager()->get('viewhelpermanager')->get('Url');

            // Don't use config channel anymore but URL "channel"
            //if (! $fbDomain) {
                $pageTabUrl = $urlHelper('frontend/' . $game->getClassType(), array('id' => $game->getIdentifier(), 'channel' => 'facebook'), array('force_canonical' => true));
            //} else {
            //    $pageTabUrl = 'http://' . $fbDomain . $urlHelper('frontend/' . $game->getClassType(), array('id' => $game->getIdentifier()));
            //}

            // What a hack :(  wanted to change the scheme with the helper... Not that simple...
            // https URL in FB needs to end with a '/'
            $securePageTabUrl = str_replace('http://', 'https://', $pageTabUrl) . '/';

            // I check if the game was not previously associated with another Fb app
            $previousApp = $appService->getAppMapper()->findOneBy(array('pageTabSourceType' => $game->getClassType(), 'pageTabSourceId' => $game->getId()));
            if ($previousApp && $previousApp->getAppId() != $game->getFbAppId()) {
                if (!$previousApp->getIsInstalled()) {
                    $previousApp->setIsAvailable(true);
                    $previousApp->setPageTabSourceType(null);
                    $previousApp->setPageTabSourceId(null);
                    $previousApp->setPageTabSourceTitle(null);

                    $previousApp = $appService->getAppMapper()->update($previousApp);
                }
            }

            $app = $appService->getAppMapper()->findByAppId($game->getFbAppId());

            // I accept to update the App only if it's available or the request is sent by the associated object
            if ($app && ($app->getIsAvailable() || ($app->getPageTabSourceType() == $game->getClassType() && $app->getPageTabSourceId() == $game->getId()))) {
                $app->setIsAvailable(0);
                $app->setPublicationDate($game->getPublicationDate());
                $app->setCloseDate($game->getCloseDate());
                $app->setPageTabUrl($pageTabUrl);
                $app->setSecurePageTabUrl($securePageTabUrl);
                $app->setPageTabTitle($game->getFbPageTabTitle());
                $app->setPageTabImage($game->getFbPageTabImage());
                $app->setPageTabSourceType($game->getClassType());
                $app->setPageTabSourceTitle($game->getTitle());
                $app->setPageTabSourceId($game->getId());

                $app = $appService->getAppMapper()->update($app);

                $facebook = new \Facebook(array(
                    'appId' => $app->getAppId(),
                    'secret' => $app->getAppSecret(),
                    'cookie' => true,
                ));
                // WtF ??? getApplicationAccessToken is protected :( have to build the token by myself !
                $accessToken = $facebook->getAppId() . '|' . $facebook->getApiSecret();

                $install = $facebook->api('/'.$app->getAppId(), 'POST',
                 array(
                     'page_tab_url'          => $app->getPageTabUrl(),
                     // Fu###ing FB bug : http://developers.facebook.com/bugs/312936975465098/
                     //'secure_page_tab_url'   => str_replace('https://', '',$app->getSecurePageTabUrl()),
                     'secure_page_tab_url'   => $app->getSecurePageTabUrl(),
                     'page_tab_default_name' => $app->getPageTabTitle(),
                     'access_token'          => $accessToken
                 ));
            }
        } else {
            // I remove Facebook association with the game if any
            $app = $appService->getAppMapper()->findOneBy(array('pageTabSourceType' => $game->getClassType(), 'pageTabSourceId' => $game->getId()));
            if ($app) {
                if (!$app->getIsInstalled()) {
                    $app->setIsAvailable(true);
                    $app->setPageTabSourceType(null);
                    $app->setPageTabSourceId(null);
                    $app->setPageTabSourceTitle(null);

                    $app = $appService->getAppMapper()->update($app);
                }
            }
        }

        return true;
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                'playgroundfacebook_doctrine_em' => 'doctrine.entitymanager.orm_default',
            ),

            'invokables' => array(
                'playgroundfacebook_app_service' => 'PlaygroundFacebook\Service\App',
                'playgroundfacebook_page_service' => 'PlaygroundFacebook\Service\Page',
            ),

            'factories' => array(
                'playgroundfacebook_module_options' => function ($sm) {
                    $config = $sm->get('Configuration');

                    return new Options\ModuleOptions(isset($config['playgroundfacebook']) ? $config['playgroundfacebook'] : array());
                },
                'playgroundfacebook_app_mapper' => function ($sm) {
                    return new \PlaygroundFacebook\Mapper\App(
                        $sm->get('playgroundfacebook_doctrine_em'),
                        $sm->get('playgroundfacebook_module_options')
                    );
                },
                'playgroundfacebook_app_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $options = $sm->get('playgroundfacebook_module_options');
                    $form = new Form\App(null, $sm, $translator);
                    $app = new Entity\App();
                    $form->setInputFilter($app->getInputFilter());

                    return $form;
                },
                'playgroundfacebook_page_mapper' => function ($sm) {
                return new \PlaygroundFacebook\Mapper\Page(
                        $sm->get('playgroundfacebook_doctrine_em'),
                        $sm->get('playgroundfacebook_module_options')
                );
                },
                'playgroundfacebook_page_form' => function($sm) {
                $translator = $sm->get('translator');
                $options = $sm->get('playgroundfacebook_module_options');
                $form = new Form\Page(null, $sm, $translator);
                $page = new Entity\Page();
                $form->setInputFilter($page->getInputFilter());

                return $form;
                },
//                 'playgroundfacebook_app_page_mapper' => function ($sm) {
//                 return new \PlaygroundFacebook\Mapper\AppPage(
//                         $sm->get('playgroundfacebook_doctrine_em'),
//                         $sm->get('playgroundfacebook_module_options')
//                 );
//                 },
//                 'playgroundfacebook_app_page_form' => function($sm) {
//                 $translator = $sm->get('translator');
//                 $options = $sm->get('playgroundfacebook_module_options');
//                 $form = new Form\AppPage(null, $translator);
//                 $appPage = new Entity\AppPage();
//                 $form->setInputFilter($appPage->getInputFilter());

//                 return $form;
//                 },
            ),
        );
    }
}
