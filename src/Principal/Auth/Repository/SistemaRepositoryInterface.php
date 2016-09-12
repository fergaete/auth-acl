<?php
namespace Principal\Auth\Repository;

use Principal\Auth\Entity\Sistema;
use Principal\Auth\Repository\Exception\NotFoundException;

/**
 * Interface SistemaRepositoryInterface
 * @package Principal\Auth\Repository
 */
interface SistemaRepositoryInterface extends RepositoryInterface {

    /**
     * @param string $apiKey
     * @return Sistema
     * @throws NotFoundException
     */
    public function findByApiKey($apiKey);
}