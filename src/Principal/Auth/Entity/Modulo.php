<?php
namespace Principal\Auth\Entity;

use Principal\Auth\Entity\Collection\RecursoCollection;

/**
 * Class Modulo
 * @package Planok\Auth\Entity
 */
class Modulo extends BaseEntity {

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $nombre = "";

    /**
     * @var RecursoCollection
     */
    private $recursos;

    /**
     * @param string $nombre
     */
    public function __construct($nombre) {
        $this->nombre = (string) $nombre;
        $this->recursos = new RecursoCollection();
    }

    /**
     * @return mixed
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