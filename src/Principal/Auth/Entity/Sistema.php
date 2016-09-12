<?php
namespace Principal\Auth\Entity;

/**
 * Class Sistema
 * @package Planok\Auth\Entity
 */
class Sistema extends BaseEntity {

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param string $nombre
     * @param string $apiKey
     */
    public function __construct($nombre, $apiKey) {
        $this->nombre = (string) $nombre;
        $this->apiKey = (string) $apiKey;
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
     * @return string
     */
    public function getApiKey() {
        return $this->apiKey;
    }
}