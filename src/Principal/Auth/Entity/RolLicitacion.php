<?php
namespace Principal\Auth\Entity;

/**
 * Class RolLicitacion
 * @package Planok\Auth\Entity
 */
class RolLicitacion extends BaseEntity {

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Rol
     */
    private $rol;

    /**
     * @var int
     */
    private $idLicitacion;

    /**
     * @var int
     */
    private $idUsuario;

    /**
     * @param Rol $rol
     * @param $idLicitacion
     * @param $idUsuario
     */
    public function __construct(Rol $rol, $idLicitacion, $idUsuario) {
        $this->rol = $rol;
        $this->idLicitacion = (int) $idLicitacion;
        $this->idUsuario = (int) $idUsuario;
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return Rol
     */
    public function getRol() {
        return $this->rol;
    }

    /**
     * @return int
     */
    public function getIdLicitacion() {
        return $this->idLicitacion;
    }

    /**
     * @return int
     */
    public function getIdUsuario() {
        return $this->idUsuario;
    }

    /**
     * @param int $idLicitacion
     */
    public function setIdLicitacion($idLicitacion) {
        $this->idLicitacion = $idLicitacion;
    }

    /**
     * @param int $idUsuario
     */
    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    /**
     * @param Rol $rol
     */
    public function setRol(Rol $rol) {
        $this->rol = $rol;
    }
}