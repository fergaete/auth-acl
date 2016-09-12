<?php
namespace Principal\Auth\Repository\Doctrine\ORM;

use Principal\Auth\Entity\Collection\ModuloCollection;
use Principal\Auth\Entity\Modulo;
use Principal\Auth\Repository\RepositoryInterface;

/**
 * Class ModuloRepository
 * @package Principal\Auth\Repository\Doctrine\ORM
 */
class ModuloRepository extends BaseRepository implements RepositoryInterface {

    /**
     * @var string
     */
    protected $entityClassName = Modulo::class;

    /**
     * @var string
     */
    protected $notFoundMessage = 'módulo con id %d no encontrada';

    /**
     * @param array $criteria
     * @param array | null $sort
     * @return ModuloCollection
     */
    public function findAll(array $criteria, array $sort = null) {
        return new ModuloCollection(parent::findAll($criteria, $sort));
    }

    /**
     * @param $id
     * @return Modulo
     * @throws \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function findById($id) {
        return parent::findById($id);
    }

    /**
     * @param $entity
     * @throws \InvalidArgumentException
     * @throws \Principal\Auth\Repository\Exception\UniqueConstrainException
     */
    public function save($entity) {
        if(!is_object($entity)) {
            throw new \InvalidArgumentException('parametro debe ser de tipo objeto');
        }
        if(!$entity instanceof Modulo) {
            throw new \InvalidArgumentException(sprintf('objeto debe ser de tipo %s, %s recibido como parametro', Accion::class, get_class($entity)));
        }
        parent::save($entity);
    }
}