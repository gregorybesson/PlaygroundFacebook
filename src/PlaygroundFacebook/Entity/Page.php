<?php

namespace PlaygroundFacebook\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="facebook_page")
 */
class Page implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="App", inversedBy="pages", cascade={"persist"})
     * @ORM\JoinTable(name="facebook_page_app",
     *      joinColumns={@ORM\JoinColumn(name="page_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="app_id", referencedColumnName="id")}
     *      )
     */
    private $apps;

    /**
     * @ORM\Column(name="page_id", type="string", length=255, unique=true, nullable=false)
     */
    protected $pageId;

    /**
     * @ORM\Column(name="page_name", type="string", length=255, nullable=true)
     */
    protected $pageName;

    /**
     * @ORM\Column(name="page_link", type="string", length=255, nullable=true)
     */
    protected $pageLink;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->apps = new ArrayCollection();
    }

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
     * @return Page
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
     * @param $pageId
     * @return Page
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * @return string
     */
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * @param $pageName
     * @return Page
     */
    public function setPageName($pageName)
    {
        $this->pageName = $pageName;

        return $this;
    }

    /**
     * @return string
     */
    public function getPageLink()
    {
        return $this->pageLink;
    }

    /**
     * @param $pageLink
     * @return Page
     */
    public function setPageLink($pageLink)
    {
        $this->pageLink = $pageLink;

        return $this;
    }

    /**
     * @param $createdAt
     * @return Page
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
     * @return Page
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
     * @return Doctrine\ORM\PersistentCollection
     */
    public function getApps()
    {
        return $this->apps;
    }

    /**
     * frm collection solution
     * @param unknown_type $apps
     */
    public function setApps(ArrayCollection $apps)
    {
        $this->apps = $apps;

        return $this;
    }

    /**
     * Add apps to the page.
     *
     * @param ArrayCollection $apps
     *
     * @return void
     */
    public function addApps(ArrayCollection $apps)
    {
        foreach ($apps as $app) {
            $app->addPage($this);
            $this->apps->add($app);
        }
    }

    /**
     * Remove apps from the page.
     *
     * @param ArrayCollection $apps
     *
     * @return void
     */
    public function removeApps(ArrayCollection $apps)
    {
        foreach ($apps as $app) {
            $app->removePage($this);
            $this->apps->removeElement($app);
        }
    }

    /**
     * Add a single app to the page.
     *
     * @param App $app
     *
     * @return void
     */
    public function addApp($app)
    {
        $this->apps[] = $app;
    }

    /**
     * Remove a single app from the page.
     *
     * @param App $app
     *
     * @return void
     */
    public function removeApp($app)
    {
        $this->apps->removeElement($app);
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
        $this->pageId = (isset($data['pageId'])) ? $data['pageId'] : null;
        $this->pageName = (isset($data['pageName'])) ? $data['pageName'] : null;
        $this->pageLink = (isset($data['pageLink'])) ? $data['pageLink'] : null;

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
            $inputFilter->add($factory->createInput(array('name' => 'pageIdRetrieved', 'required' => false, 'filters' => array(array('name' => 'Int'),),)));
            $inputFilter->add($factory->createInput(array('name' => 'apps', 'required' => false)));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
