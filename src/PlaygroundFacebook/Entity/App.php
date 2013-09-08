<?php

namespace PlaygroundFacebook\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="facebook_app")
 */
class App implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="app_id", type="string", length=255, unique=true, nullable=false)
     */
    protected $appId;

    /**
     * @ORM\Column(name="app_secret", type="string", length=255, nullable=false)
     */
    protected $appSecret;

    /**
     * @ORM\Column(name="publication_date", type="datetime", nullable=true)
     */
    protected $publicationDate;

    /**
     * @ORM\Column(name="close_date", type="datetime", nullable=true)
     */
    protected $closeDate;

    /**
     * @ORM\Column(name="is_available", type="boolean")
     */
    protected $isAvailable = 1;

    /**
     * @ORM\Column(name="is_installed", type="boolean")
     */
    protected $isInstalled = 0;

    /**
     * @ORM\Column(name="page_tab_url", type="string", length=255, nullable=true)
     */
    protected $pageTabUrl;

    /**
     * @ORM\Column(name="secure_page_tab_url", type="string", length=255, nullable=true)
     */
    protected $securePageTabUrl;

    /**
     * @ORM\Column(name="page_tab_title", type="string", length=255, nullable=true)
     */
    protected $pageTabTitle;

    /**
     * @ORM\Column(name="page_tab_image", type="string", length=255, nullable=true)
     */
    protected $pageTabImage;

    /**
     * @ORM\Column(name="page_tab_source_type", type="string", length=255, nullable=true)
     */
    protected $pageTabSourceType;

    /**
     * @ORM\Column(name="page_tab_source_title", type="string", length=255, nullable=true)
     */
    protected $pageTabSourceTitle;

    /**
     * @ORM\Column(name="page_tab_source_id", type="integer", nullable=true)
     */
    protected $pageTabSourceId;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /** @PrePersist */
    public function createChrono()
    {
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");
    }

    /** @PreUpdate */
    public function updateChrono()
    {
        $this->updatedAt = new \DateTime("now");
    }

    /**
     * @param $id
     * @return Block|mixed
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $appId
     * @return App
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param $appSecret
     * @return App
     */
    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * @return mixed
     */
    public function getIsAvailable()
    {
        return $this->isAvailable;
    }

    /**
     * @param $isAvailable
     * @return Block
     */
    public function setIsAvailable($isAvailable)
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsInstalled()
    {
        return $this->isInstalled;
    }

    /**
     * @param $isInstalled
     * @return Block
     */
    public function setIsInstalled($isInstalled)
    {
        $this->isInstalled = $isInstalled;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageTabUrl()
    {
        return $this->pageTabUrl;
    }

    /**
     * @param $pageTabUrl
     * @return Block
     */
    public function setPageTabUrl($pageTabUrl)
    {
        $this->pageTabUrl = $pageTabUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSecurePageTabUrl()
    {
        return $this->securePageTabUrl;
    }

    /**
     * @param $securePageTabUrl
     * @return Block
     */
    public function setSecurePageTabUrl($securePageTabUrl)
    {
        $this->securePageTabUrl = $securePageTabUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageTabTitle()
    {
        return $this->pageTabTitle;
    }

    /**
     * @param $pageTabtitle
     * @return Block
     */
    public function setPageTabTitle($pageTabTitle)
    {
        $this->pageTabTitle = $pageTabTitle;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageTabImage()
    {
        return $this->pageTabImage;
    }

    /**
     * @param $pageTabImage
     * @return Block
     */
    public function setPageTabImage($pageTabImage)
    {
        $this->pageTabImage = $pageTabImage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageTabSourceType()
    {
        return $this->pageTabSourceType;
    }

    /**
     * @param $pageTabSourceType
     * @return Block
     */
    public function setPageTabSourceType($pageTabSourceType)
    {
        $this->pageTabSourceType = $pageTabSourceType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageTabSourceTitle()
    {
        return $this->pageTabSourceTitle;
    }

    /**
     * @param $pageTabSourceTitle
     * @return Block
     */
    public function setPageTabSourceTitle($pageTabSourceTitle)
    {
        $this->pageTabSourceTitle = $pageTabSourceTitle;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageTabSourceId()
    {
        return $this->pageTabSourceId;
    }

    /**
     * @param $pageTabSourceId
     * @return Block
     */
    public function setPageTabSourceId($pageTabSourceId)
    {
        $this->pageTabSourceId = $pageTabSourceId;

        return $this;
    }

    /**
     *
     * @return the $publicationDate
     */
    public function getPublicationDate ()
    {
        return $this->publicationDate;
    }

    /**
     *
     * @param field_type $publicationDate
     */
    public function setPublicationDate ($publicationDate)
    {
        $this->publicationDate = $publicationDate;
    }

    /**
     *
     * @return the $closeDate
     */
    public function getCloseDate ()
    {
        return $this->closeDate;
    }

    /**
     *
     * @param field_type $closeDate
     */
    public function setCloseDate ($closeDate)
    {
        $this->closeDate = $closeDate;
    }

    /**
     * @param $createdAt
     * @return Block
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param $updatedAt
     * @return Block
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        $this->appId = (isset($data['appId'])) ? $data['appId'] : null;
        $this->appSecret = (isset($data['appSecret'])) ? $data['appSecret'] : null;

    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array('name' => 'id', 'required' => true, 'filters' => array(array('name' => 'Int'),),)));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
