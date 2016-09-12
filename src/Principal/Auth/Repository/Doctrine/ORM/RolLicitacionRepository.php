<?php
namespace Principal\Auth\Repository\Doctrine\ORM;

use Principal\Auth\Entity\Collection\RolLicitacionCollection;
use Principal\Auth\Entity\Recurso;
use Principal\Auth\Entity\RolLicitacion;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RolLicitacionRepositoryInterface;

/**
 * Class RolLicitacionRepository
 * @package Principal\Auth\Repository\Doctrine\ORM
 */
class RolLicitacionRepository extends BaseRepository implements RolLicitacionRepositoryInterface {

    /**
     * @var string
     */
    protected $entityClassName = RolLicitacion::class;

    /**
     * @var string
     */
    protected $notFoundMessage = 'rolLicitacion con id %s no encontrado';

    /**
     * @var string
     */
    protected $duplicatedMessage = 'rolLicitacion duplicado';

    /**
     * @param array $criteria
     * @param array | null $sort
     * @return RolLicitacionCollection
     */
    public function findAll(array $criteria, array $sort = null) {
        $query = $this->em->createQueryBuilder()
            ->select('this')
            ->from(RolLicitacion::class, 'this')
            ->join('this.rol', 'rol');

        $count = 0;
        foreach ($criteria as $fieldName => $value) {
            if($count == 0) {
                $query->where($fieldName . '=' . $value);
            }
            else {
                $query->andWhere($fieldName . '=' . $value);
            }
            $count++;
        }

        if(is_array($sort)) {
            foreach($sort as $fieldName => $order) {
                $query->orderBy($fieldName, $order);
            }
        }

        return new RolLicitacionCollection($query->getQuery()->getResult());
    }

    /**
     * @param int $id
     * @return RolLicitacion
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
            throw new \InvalidArgumentException('parametro debe ser un objeto');
        }

        if(!$entity instanceof RolLicitacion) {
            throw new \InvalidArgumentException(sprintf('parametro debe ser de tipo %s, %s recibido', RolLicitacion::class, get_class($entity)));
        }

        parent::save($entity);
    }

    /**
     * @param int $idLicitacion
     * @param int $idUsuario
     * @param Recurso $recurso
     * @return RolLicitacionCollection
     */
    public function findByIdLicitacionAndIdUsuarioAndRecurso($idLicitacion, $idUsuario, Recurso $recurso) {
        $results = $this->em->createQueryBuilder()
            ->select('rl')
            ->from(RolLicitacion::class, 'rl')
            ->join('rl.rol', 'rol')
            ->join('rol.permisos', 'permiso')
            ->join('permiso.recurso', 'recurso')
            ->join('recurso.modulo', 'modulo')
            ->join('recurso.accion', 'accion')
            ->where('rl.idUsuario = :idUsuario')
            ->andWhere('rl.idLicitacion = :idLicitacion')
            ->andWhere('modulo.nombre = :nombreModulo')
            ->andWhere('accion.nombre = :nombreAccion')
            ->setParameters(array(
                'idLicitacion' => (int) $idLicitacion,
                'idUsuario'    => (int) $idUsuario,
                'nombreModulo' => $recurso->getModulo()->getNombre(),
                'nombreAccion' => $recurso->getAccion()->getNombre()
            ))->getQuery()->getResult();

        return new RolLicitacionCollection($results);
    }
}