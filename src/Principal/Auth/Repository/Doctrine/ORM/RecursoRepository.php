<?php
namespace Principal\Auth\Repository\Doctrine\ORM;

use Principal\Auth\Entity\Collection\RecursoCollection;
use Principal\Auth\Entity\Modulo;
use Principal\Auth\Entity\Recurso;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\RecursoRepositoryInterface;
use Principal\Auth\Repository\RepositoryInterface;

/**
 * Class RecursoRepository
 * @package Principal\Auth\Repository\Doctrine\ORM
 */
class RecursoRepository extends BaseRepository implements RepositoryInterface {

    /**
     * @var string
     */
    protected $entityClassName = Recurso::class;

    /**
     * @var string
     */
    protected $notFoundMessage = 'recurso con id %s no encontrado';

    /**
     * @param array $criteria
     * @param array | null $sort
     * @return RecursoCollection
     */
    public function findAll(array $criteria, array $sort = null) {
        return new RecursoCollection(parent::findAll($criteria, $sort));
    }

    /**
     * @param $id
     * @return Recurso
     * @throws \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function findById($id) {
        return parent::findById($id);
    }

    /**
     * @param $entity
     * @throws \Principal\Auth\Repository\Exception\UniqueConstrainException
     */
    public function save($entity) {
        if(!is_object($entity)) {
            throw new \InvalidArgumentException('parametro debe ser un objeto');
        }

        if(!$entity instanceof Recurso) {
            throw new \InvalidArgumentException(sprintf('parametro es de tipo %s se espera %s', get_class($entity), Recurso::class));
        }

        parent::save($entity);
    }
}