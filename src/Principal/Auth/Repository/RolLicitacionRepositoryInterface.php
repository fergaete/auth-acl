<?php
namespace Principal\Auth\Repository;

use Principal\Auth\Entity\Collection\RolLicitacionCollection;
use Principal\Auth\Entity\Recurso;
use Principal\Auth\Repository\Exception\NotFoundException;

/**
 * Interface RolLicitacionRepositoryInterface
 * @package Principal\Auth\Repository
 */
interface RolLicitacionRepositoryInterface extends RepositoryInterface {

    /**
     * @param int $idLicitacion
     * @param int $idUsuario
     * @param Recurso $recurso
     * @return RolLicitacionCollection
     */
    public function findByIdLicitacionAndIdUsuarioAndRecurso($idLicitacion, $idUsuario, Recurso $recurso);

}