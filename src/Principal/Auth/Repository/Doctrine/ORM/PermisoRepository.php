<?php
namespace Principal\Auth\Repository\Doctrine\ORM;

use Principal\Auth\Entity\Collection\PermisoCollection;
use Principal\Auth\Entity\Permiso;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\RepositoryInterface;

/**
 * Class PermisoRepository
 * @package Principal\Auth\Repository\Doctrine\ORM
 */
class PermisoRepository extends BaseRepository implements RepositoryInterface {

    /**
     * @var string
     */
    protected $entityClassName = Permiso::class;

    /**
     * @var string
     */
    protected $notFoundMessage = 'permiso con id %s no encontrado';

    /**
     * @var string
     */
    protected $duplicatedMessage = 'permiso ya existe';

    /**
     * @param array $criteria
     * @param array | null $sort
     * @return PermisoCollection
     */
    public function findAll(array $criteria, array $sort = null) {
        return new PermisoCollection(parent::findAll($criteria, $sort));
    }

    /**
     * @param int $id
     * @return Permiso
     * @throws NotFoundException
     */
    public function findById($id) {
        return parent::findById($id);
    }

    /**
     * @param $entity
     */
    public function save($entity) {
        if(!is_object($entity)) {
            throw new \InvalidArgumentException('parametro debe ser objeto');
        }
        if(!$entity instanceof Permiso) {
            throw new \InvalidArgumentException(sprintf('parametro debe ser de tipo %s, se recibio %s', Permiso::class, get_class($entity)));
        }

        parent::save($entity);
    }
}