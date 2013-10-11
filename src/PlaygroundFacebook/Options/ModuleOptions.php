<?php

namespace PlaygroundFacebook\Options;

class ModuleOptions
{
    /**
     * @var string
     */
    protected $appEntityClass = 'PlaygroundFacebook\Entity\App';

    /**
     * @var string
     */
    protected $pageEntityClass = 'PlaygroundFacebook\Entity\Page';

    /**
     * @var string
     */
    protected $appPageEntityClass = 'PlaygroundFacebook\Entity\AppPage';

    /**
     * @var bool
     */
    protected $enableDefaultEntities = true;

    protected $appMapper = 'PlaygroundFacebook\Mapper\App';

    protected $pageMapper = 'PlaygroundFacebook\Mapper\Page';

    protected $appPageMapper = 'PlaygroundFacebook\Mapper\AppPage';

    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;

    public function setAppMapper($appMapper)
    {
        $this->appMapper = $appMapper;
    }

    public function getAppMapper()
    {
        return $this->appMapper;
    }

    public function setPageMapper($pageMapper)
    {
        $this->pageMapper = $pageMapper;
    }

    public function getPageMapper()
    {
        return $this->pageMapper;
    }

    public function setAppPageMapper($appPageMapper)
    {
        $this->appPageMapper = $appPageMapper;
    }

    public function getAppPageMapper()
    {
        return $this->appPageMapper;
    }

    /**
     * set app entity class name
     *
     * @param  string        $appEntityClass
     * @return ModuleOptions
     */
    public function setAppEntityClass($appEntityClass)
    {
        $this->appEntityClass = $appEntityClass;

        return $this;
    }

    /**
     * get app entity class name
     *
     * @return string
     */
    public function getAppEntityClass()
    {
        return $this->appEntityClass;
    }

    /**
     * set page entity class name
     *
     * @param  string        $appEntityClass
     * @return ModuleOptions
     */
    public function setPageEntityClass($pageEntityClass)
    {
        $this->pageEntityClass = $pageEntityClass;

        return $this;
    }

    /**
     * get page entity class name
     *
     * @return string
     */
    public function getPageEntityClass()
    {
        return $this->pageEntityClass;
    }

    /**
     * set app-page entity class name
     *
     * @param  string        $appEntityClass
     * @return ModuleOptions
     */
    public function setAppPageEntityClass($appPageEntityClass)
    {
        $this->appPageEntityClass = $appPageEntityClass;

        return $this;
    }

    /**
     * get app-page entity class name
     *
     * @return string
     */
    public function getAppPageEntityClass()
    {
        return $this->appPageEntityClass;
    }

    /**
     * @param boolean $enableDefaultEntities
     */
    public function setEnableDefaultEntities($enableDefaultEntities)
    {
        $this->enableDefaultEntities = $enableDefaultEntities;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableDefaultEntities()
    {
        return $this->enableDefaultEntities;
    }
}
