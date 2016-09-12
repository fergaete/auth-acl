<?php
namespace Principal\Auth\Repository;

use Principal\Auth\Entity\Collection\BaseCollection;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;

/**
 * Interface RepositoryInterface
 * @package Principal\Auth\Repository
 */
interface RepositoryInterface {

    /**
     * @param array $criteria
     * @param array | null $sort
     * @return BaseCollection
     */
    public function findAll(array $criteria, array $sort = null);

    /**
     * @param int $id
     * @return mixed
     * @throws NotFoundException
     */
    public function findById($id);

    /**
     * @param $entity
     * @throws \InvalidArgumentException
     * @throws UniqueConstrainException
     */
    public function save($entity);
}