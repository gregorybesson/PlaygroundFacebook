<?php

namespace PlaygroundFacebook\Mapper;

use Doctrine\ORM\EntityManager;
use ZfcBase\Mapper\AbstractDbMapper;
use PlaygroundFacebook\Options\ModuleOptions;
use Zend\Stdlib\Hydrator\HydratorInterface;

class Page extends AbstractDbMapper
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \PlaygroundFacebook\Options\ModuleOptions
     */
    protected $options;

    public function __construct(EntityManager $em, ModuleOptions $options)
    {
        $this->em      = $em;
        $this->options = $options;
    }

    public function findAll()
    {
        $er = $this->em->getRepository($this->options->getPageEntityClass());

        return $er->findAll();
    }

    /**
     * @param $id
     * @return object
     */
    public function findById($id)
    {
        $er = $this->em->getRepository($this->options->getPageEntityClass());
        $entity = $er->find($id);

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));

        return $entity;
    }

    public function findByPageId($pageId)
    {
        $er = $this->em->getRepository($this->options->getPageEntityClass());

        return $er->findOneBy(array('pageId' => $pageId));
    }

    public function findBy($array)
    {
        $er = $this->em->getRepository($this->options->getPageEntityClass());

        return $er->findBy($array);
    }

    public function findOneBy($array)
    {
        $er = $this->em->getRepository($this->options->getPageEntityClass());

        return $er->findOneBy($array);
    }

    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    protected function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
