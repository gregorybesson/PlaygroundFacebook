<?php

namespace PlaygroundFacebook\Options;

class ModuleOptions
{
    /**
     * @var string
     */
    protected $appEntityClass = 'PlaygroundFacebook\Entity\App';

    /**
     * @var bool
     */
    protected $enableDefaultEntities = true;

    protected $appMapper = 'PlaygroundFacebook\Mapper\App';

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
