<?php

namespace PlaygroundFacebook\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    protected $options, $facebookService;

    public function indexAction()
    {

        $facebooks = $this->getFacebookService()->getActiveFacebooks();
        if (is_array($facebooks)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($facebooks));
        } else {
            $paginator = $facebooks;
        }

        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return new ViewModel(array('facebooks' => $paginator));
    }

    public function getFacebookService()
    {
        if (!$this->facebookService) {
            $this->facebookService = $this->getServiceLocator()->get('playgroundfacebook_facebook_service');
        }

        return $this->facebookService;
    }

    public function setFacebookService(FacebookService $facebookService)
    {
        $this->facebookService = $facebookService;

        return $this;
    }
}
