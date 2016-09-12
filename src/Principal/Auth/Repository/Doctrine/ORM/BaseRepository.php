<?php
namespace Principal\Auth\Repository\Doctrine\ORM;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;

/**
 * Class BaseRepository
 * @package Principal\Auth\Repository\Doctrine\ORM
 */
abstract class BaseRepository {

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $entityClassName;

    /**
     * @var string
     */
    protected $notFoundMessage;

    /**
     * @var string
     */
    protected $duplicatedMessage;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    /**
     * @param array $criteria
     * @param array|null $sort
     * @return mixed
     */
    public function findAll(array $criteria, array $sort = null) {
        return $this->em->getRepository($this->entityClassName)->findBy($criteria, $sort);
    }

    /**
     * @param $id
     * @return mixed
     * @throws NotFoundException
     */
    public function findById($id) {
        $id = (int) $id;
        $entity = $this->em->find($this->entityClassName, $id);
        if(is_null($entity)) {
            throw new NotFoundException(sprintf($this->notFoundMessage, $id));
        }

        return $entity;
    }

    /**
     * @param $entity
     * @throws UniqueConstrainException
     */
    public function save($entity) {
        try {
            $this->em->persist($entity);
            $this->em->flush();
        }
        catch(UniqueConstraintViolationException $ex) {
            throw new UniqueConstrainException($this->duplicatedMessage, 0, $ex);
        }
    }
}