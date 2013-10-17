<?php

namespace PlaygroundFacebook\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundFacebook\Options\ModuleOptions;
use Zend\View\Model\ViewModel;

class PageController extends AbstractActionController
{
    protected $options, $pageMapper, $adminPageService;

    public function listAction()
    {
        $config = $this->getAdminPageService()->getServiceManager()->get('config');
        if (isset($config['facebook'])) {
            $platformFbAppId     = $config['facebook']['fb_appid'];
            $platformFbAppSecret = $config['facebook']['fb_secret'];
        }

        $pages               = $this->getPageMapper()->findAll();
        $fbPages             = array();
        $fbLogged            = false;
        $fbAllowed           = false;
        $fbLoginUrl          = '';
        $paginator           = array();
        $pages_array         = array();
        $user = null;

        // Create our Application instance with the FB App associated with the plateform
        $facebookPtf = new \Facebook(array(
            'appId' => $platformFbAppId,
            'secret' => $platformFbAppSecret,
            'cookie' => false,
        ));

        $user = $facebookPtf->getUser();

        // Retrieve and update information about registered pages (if admin user is connected to Facebook)

        if ($user){

            $fbLogged = true;
            foreach ($pages as $page) {

                $page_info = $this->getAdminPageService()->getPageInfoFromFacebookAccount( array('pageId' => $page->getPageId()));

                if (isset($page_info['pageName'])){
                    $page->setPageName($page_info['pageName']);
                }
                if (isset($page_info['pageLink'])){
                    $page->setPageLink($page_info['pageLink']);
                }
                $this->getPageMapper()->update($page);

            }

            $pages = $this->getPageMapper()->findAll();

        // Build Facebook login URL (if admin user is not connected to Facebook)

        } else {

            $fbLoginUrl = $facebookPtf->getLoginUrl(array('scope' => 'manage_pages'));
        }

        foreach ($pages as $page){
            $pages_array[] = $page->getArrayCopy();
        }

        if (is_array($pages_array)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($pages_array));
            $paginator->setItemCountPerPage(10);
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $pages_array;
        }

        return array(
            'pages'      => $paginator,
            'fbLogged'  => $fbLogged,
            'fbAllowed' => $fbAllowed,
            'fbLoginUrl'=> $fbLoginUrl
        );
    }

    public function createAction()
    {
        $form = $this->getServiceLocator()->get('playgroundfacebook_page_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/facebook/page/create', array('pageId' => 0)));
        $form->setAttribute('method', 'post');

        // Get the pages administred by the Facebook user (if admin is connected to Facebook)

        $pagesFromFacebook = $this->getAdminPageService()->getPagesFromFacebookAccount();

        // Construct the select box for app selection

        $userFbPagesOptions = array();

        foreach ($pagesFromFacebook as $page) {

            $userFbPagesOptions[$page['pageId']] = $page['pageName'] . ' - '.$page['pageId'];
        }

        if (sizeof($userFbPagesOptions)){
            $form->get('pageIdRetrieved')->setValueOptions($userFbPagesOptions);
        }


        $page = new \PlaygroundFacebook\Entity\Page();
        $form->bind($page);

        // Persist the page in Playground, and redirect to the list of pages
        $request = $this->getRequest();

        if ($request->isPost()) {

            $data = $request->getPost()->toArray();
            // Get more information about the page, from Facebook (if admin user is connected to Facebook)

            $data_extended = $this->getAdminPageService()->getPageInfoFromFacebookAccount($data);

            $page = $this->getAdminPageService()->create($data_extended, $page);
            if ($page) {
                $this->flashMessenger()->setNamespace('playgroundfacebook')->addMessage('La page Facebook a été importée');
                return $this->redirect()->toRoute('admin/facebook/page/list');
            }
        }

        // Display the page form for page creation

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-facebook/page/edit');

        return $viewModel->setVariables(array('form' => $form, 'isCreate' => true));
    }

    public function editAction()
    {
        $pageId = $this->getEvent()->getRouteMatch()->getParam('pageId');
        $page = $this->getPageMapper()->findById($pageId);
        $form = $this->getServiceLocator()->get('playgroundfacebook_page_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/facebook/page/edit', array('pageId' => $pageId)));
        $form->setAttribute('method', 'post');

        $form->bind($page);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();

            // Get more information about the page, from Facebook (if admin user is connected to Facebook)

            $data_extended = $this->getAdminPageService()->getPageInfoFromFacebookAccount($data);

            $page = $this->getAdminPageService()->edit($data_extended, $page);
            if ($page) {
                $this->flashMessenger()->setNamespace('playgroundfacebook')->addMessage('La page a été éditée');

                return $this->redirect()->toRoute('admin/facebook/page/list');
            }
        }

        return array('form' => $form, 'isCreate' => false);
    }

    public function removeAction()
    {
        $pageId = $this->getEvent()->getRouteMatch()->getParam('pageId');
        $page = $this->getPageMapper()->findById($pageId);
        if ($page) {
            $this->getAdminPageService()->remove($page);
            $this->flashMessenger()->setNamespace('playgroundfacebook')->addMessage('Page supprimée');
        }

        return $this->redirect()->toRoute('admin/facebook/page/list');
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
