<?php
namespace Principal\Auth\Repository\Doctrine\ORM;

use Principal\Auth\Entity\Accion;
use Principal\Auth\Entity\Collection\AccionCollection;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;

/**
 * Class AccionRepository
 * @package Principal\Auth\Repository\Doctrine\ORM
 */
class AccionRepository extends BaseRepository implements RepositoryInterface {

    /**
     * @var string
     */
    protected $entityClassName   = Accion::class;

    /**
     * @var string
     */
    protected $notFoundMessage   = 'accion con id %d no encontrada';

    /**
     * @var string
     */
    protected $duplicatedMessage = 'acción ya existe';

    /**
     * @param array $criteria
     * @param array | null $sort
     * @return AccionCollection
     */
    public function findAll(array $criteria, array $sort = null) {
        return new AccionCollection(parent::findAll($criteria, $sort));
    }

    /**
     * @param $id
     * @return Accion
     * @throws NotFoundException
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
        if(!$entity instanceof Accion) {
            throw new \InvalidArgumentException(sprintf('objeto debe ser de tipo %s, %s recibido como parametro', Accion::class, get_class($entity)));
        }
        parent::save($entity);
    }
}