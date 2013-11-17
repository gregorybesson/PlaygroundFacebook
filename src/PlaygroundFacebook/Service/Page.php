<?php
namespace PlaygroundFacebook\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
// use Zend\Stdlib\Hydrator\ClassMethods;
use PlaygroundFacebook\Options\ModuleOptions;

class Page extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     *
     * @var PageMapperInterface
     */
    protected $pageMapper;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     *
     * @var PageServiceOptionsInterface
     */
    protected $options;

    public function create(array $data, $page)
    {

        // Page ID chosen from the retrieved page list is prior to page id entered by the user
        $pageId = $data['pageId'];
        $pageId = 'pageId';
        if (isset($data['pageIdRetrieved']) && $data['pageIdRetrieved']) {
            $pageId = $data['pageIdRetrieved'];
            $pageIdField = 'pageIdRetrieved';
            $data['pageId'] = $pageId;
        }

        $form = $this->getServiceManager()->get('playgroundfacebook_page_form');
//         $form->setHydrator(new ClassMethods());
        $form->bind($page);
        $form->setData($data);

        $user = null;
        $userAccessToken = null;

        $config = $this->getServiceManager()->get('config');
        if (isset($config['facebook'])) {
            $facebookPtf = new \Facebook(array(
                'appId' => $config['facebook']['fb_appid'],
                'secret' => $config['facebook']['fb_secret'],
                'cookie' => false
            ));

            $user = $facebookPtf->getUser();

            $userAccessToken = $facebookPtf->getAccessToken();
        }

        if ($user) {
            try {
                $page_details = $facebookPtf->api('/' . $pageId, 'GET', array(
                    'access_token' => $userAccessToken
                ));
            } catch (\FacebookApiException $e) {
                $form->get($pageIdField)->setMessages(array(
                    'Cette page n\'existe pas ou l\'identifiant est incorrect'
                ));

                return false;
            }
        }

        if (! $form->isValid()) {
            return false;
        }

        return $this->getPageMapper()->insert($page);
    }

    public function edit(array $data, $page)
    {
        $pageId = $data['pageId'];

        $form = $this->getServiceManager()->get('playgroundfacebook_page_form');
//         $form->setHydrator(new ClassMethods());

        if (!isset($data['apps'])){
            $data['apps'] = array();
        }

        $form->bind($page);
        $form->setData($data);

        $config = $this->getServiceManager()->get('config');
        if (isset($config['facebook'])) {
            $facebookPtf = new \Facebook(array(
                'appId' => $config['facebook']['fb_appid'],
                'secret' => $config['facebook']['fb_secret'],
                'cookie' => false
            ));

            $user = $facebookPtf->getUser();

            $userAccessToken = $facebookPtf->getAccessToken();
        }

        if ($user) {
            try {
                $page_details = $facebookPtf->api('/' . $pageId, 'GET', array(
                    'access_token' => $userAccessToken
                ));
            } catch (\FacebookApiException $e) {
                $form->get($pageIdField)->setMessages(array(
                    'Cette page n\'existe pas ou l\'identifiant est incorrect'
                ));

                return false;
            }
        }

        if (! $form->isValid()) {
            return false;
        }

        return $this->getPageMapper()->update($page);
    }

    public function remove($page)
    {
        return $this->getPageMapper()->remove($page);
    }

    /**
     * getPagesFromFacebookAccount
     *
     * @return Array
     */
    public function getPagesFromFacebookAccount()
    {
        $registeredFbPages = array();
        $returnedFbPages = array();

        // Get all Facebook pages already persisted into Playground

        $pgPages = $this->getPageMapper()->findAll();
        foreach ($pgPages as $page) {
            $registeredFbPages[$page->getPageId()] = true;
        }

        // Retrieve user Facebook identifier, if user is connected to Facebook

        $user = null;

        $config = $this->getServiceManager()->get('config');
        if (isset($config['facebook'])) {
            $facebookPtf = new \Facebook(array(
                'appId' => $config['facebook']['fb_appid'],
                'secret' => $config['facebook']['fb_secret'],
                'cookie' => false
            ));

            $user = $facebookPtf->getUser();
        }

        // Try to retrieve pages from Facebook, if user is connected to Facebook

        if ($user) {

            try {

                // Retrieve pages administred by the user

                $userFbPages = $facebookPtf->api('/me/accounts?fields=id,name,link', 'GET');

                if (isset($userFbPages['data']) && is_array($userFbPages['data'])) {
                    foreach ($userFbPages['data'] as $userFbPage) {
                        if (array_key_exists('id', $userFbPage)) {

                            if (! array_key_exists($userFbPage['id'], $registeredFbPages)) { // ignore pages already persisted in Playground

                                $returnedFbPages[] = array(
                                    'pageId' => $userFbPage['id'],
                                    'pageName' => array_key_exists('id', $userFbPage) ? $userFbPage['name'] : '',
                                    'pageLink' => array_key_exists('link', $userFbPage) ? $userFbPage['link'] : ''
                                );
                            }
                        }
                    }
                }
            } catch (\FacebookApiException $e) {
                
            }
        }

        return $returnedFbPages;
    }

    /**
     * getPageFromFacebookAccount
     *
     * @return Array
     */
    public function getPageInfoFromFacebookAccount(array $data)
    {

        // Retrieve user Facebook identifier, if admin user is connected to Facebook
        $user = null;

        $config = $this->getServiceManager()->get('config');
        if (isset($config['facebook'])) {
            $facebookPtf = new \Facebook(array(
                'appId' => $config['facebook']['fb_appid'],
                'secret' => $config['facebook']['fb_secret'],
                'cookie' => false
            ));

            $user = $facebookPtf->getUser();
        }

        // Get the right page ID : page ID chosen from the retrieved page list is prior to page id entered by the user

        $pageId = '';
        if (isset($data['pageId']) && $data['pageId']) {
            $pageId = $data['pageId'];;
        }
        if (isset($data['pageIdRetrieved']) && $data['pageIdRetrieved']) {
            $pageId = $data['pageIdRetrieved'];
            $data['pageId'] = $pageId;
            // unset($data['pageIdRetrieved']);
        }


        // Try to retrieve page info from Facebook, if user is connected to Facebook

        if ($user) {

            try {

                // Retrieve pages administred by the user

                $userFbPage = $facebookPtf->api('/' . $pageId . '?fields=id,name,link', 'GET');

                if (isset($userFbPage) && is_array($userFbPage)) {

                    if (array_key_exists('name', $userFbPage)) {
                        $data['pageName'] = $userFbPage['name'];
                    }
                    if (array_key_exists('link', $userFbPage)) {
                        $data['pageLink'] = $userFbPage['link'];
                    }
                }
            } catch (\FacebookApiException $e) {}
        }

        return $data;
    }

    /**
     * getPageMapper
     *
     * @return PageMapperInterface
     */
    public function getPageMapper()
    {
        if (null === $this->pageMapper) {
            $this->pageMapper = $this->getServiceManager()->get('playgroundfacebook_page_mapper');
        }

        return $this->pageMapper;
    }

    /**
     * setAppMapper
     *
     * @param PageMapperInterface $pageMapper
     * @return App
     */
    public function setPageMapper(PageMapperInterface $pageMapper)
    {
        $this->pageMapper = $pageMapper;

        return $this;
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
