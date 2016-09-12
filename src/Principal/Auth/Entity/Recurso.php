<?php
namespace Principal\Auth\Entity;

use Principal\Auth\Entity\Collection\PermisoCollection;

/**
 * Class Recurso
 * @package Principal\Auth\Entity
 */
class Recurso extends BaseEntity {

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Accion
     */
    private $accion;

    /**
     * @var Modulo
     */
    private $modulo;

    /**
     * @var PermisoCollection
     */
    private $permisos;

    /**
     * @param Modulo $modulo
     * @param Accion $accion
     */
    public function __construct(Modulo $modulo, Accion $accion) {
        $this->modulo = $modulo;
        $this->accion = $accion;
        $this->permisos = new PermisoCollection();
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return Accion
     */
    public function getAccion() {
        return $this->accion;
    }

    /**
     * @return Modulo
     */
    public function getModulo() {
        return $this->modulo;
    }
}