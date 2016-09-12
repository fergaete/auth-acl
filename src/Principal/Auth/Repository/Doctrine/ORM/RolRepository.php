<?php
namespace Principal\Auth\Repository\Doctrine\ORM;

use Principal\Auth\Entity\Collection\RolCollection;
use Principal\Auth\Entity\Rol;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;

/**
 * Class RolRepository
 * @package Principal\Auth\Repository\Doctrine\ORM
 */
class RolRepository extends BaseRepository implements RepositoryInterface {

    /**
     * @var string
     */
    protected $entityClassName = Rol::class;

    /**
     * @var string
     */
    protected $notFoundMessage = 'rol con id %d no encontrado';

    /**
     * @var string
     */
    protected $duplicatedMessage = 'rol ya existe';

    /**
     * @param array $criteria
     * @param array | null $sort
     * @return RolCollection
     */
    public function findAll(array $criteria, array $sort = null) {
        return new RolCollection(parent::findAll($criteria, $sort));
    }

    /**
     * @param int $id
     * @return Rol
     * @throws \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function findById($id) {
        return parent::findById($id);
    }

    /**
     * @param $entity
     * @throws \InvalidArgumentException
     * @throws UniqueConstrainException
     */
    public function save($entity) {
        if(!is_object($entity)) {
            throw new \InvalidArgumentException('parametro debe ser de tipo objeto');
        }
        if(!$entity instanceof Rol) {
            throw new \InvalidArgumentException(sprintf('objeto debe ser de tipo %s, %s recibido como parametro', Rol::class, get_class($entity)));
        }
        parent::save($entity);
    }
}