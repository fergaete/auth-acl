<?php
namespace Principal\Auth\Entity;

use Principal\Auth\Entity\Collection\PermisoCollection;
use Principal\Auth\Entity\Collection\RolLicitacionCollection;

/**
 * Class Rol
 * @package Principal\Auth\Entity
 */
class Rol extends BaseEntity {

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var PermisoCollection
     */
    private $permisos;

    /**
     * @var RolLicitacionCollection
     */
    private $rolLicitaciones;

    /**
     * @param string $nombre
     */
    public function __construct($nombre) {
        $this->nombre = (string) $nombre;
        $this->permisos = new PermisoCollection();
        $this->rolLicitaciones = new RolLicitacionCollection();
    }

    /**
     * @return int|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNombre() {
        return $this->nombre;
    }

    /**
     * @param string $nombre
     */
    public function setNombre($nombre) {
        $this->nombre = (string) $nombre;
    }
}