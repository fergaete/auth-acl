<?php
namespace Principal\Auth\Entity;

/**
 * Class Permiso
 * @package Principal\Auth\Entity
 */
class Permiso extends BaseEntity {

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Rol
     */
    private $rol;

    /**
     * @var Recurso
     */
    private $recurso;

    /**
     * @param Rol $rol
     * @param Recurso $recurso
     */
    public function __construct(Rol $rol, Recurso $recurso) {
        $this->rol = $rol;
        $this->recurso = $recurso;
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return Recurso
     */
    public function getRecurso() {
        return $this->recurso;
    }

    /**
     * @return Rol
     */
    public function getRol() {
        return $this->rol;
    }
}