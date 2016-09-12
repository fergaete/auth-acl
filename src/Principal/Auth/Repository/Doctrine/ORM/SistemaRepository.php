<?php
namespace Principal\Auth\Repository\Doctrine\ORM;

use Principal\Auth\Entity\Collection\SistemaCollection;
use Principal\Auth\Entity\Sistema;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\SistemaRepositoryInterface;

/**
 * Class SistemaRepository
 * @package Principal\Auth\Repository\Doctrine\ORM
 */
class SistemaRepository extends BaseRepository implements SistemaRepositoryInterface {

    /**
     * @var string
     */
    protected $entityClassName   = Sistema::class;

    /**
     * @var string
     */
    protected $notFoundMessage   = 'sistema con id %d no encontrada';

    /**
     * @var string
     */
    protected $duplicatedMessage = 'sistema ya existe';

    /**
     * @param array $criteria
     * @param array | null $sort
     * @return SistemaCollection
     */
    public function findAll(array $criteria, array $sort = null) {
        return new SistemaCollection(parent::findAll($criteria, $sort));
    }

    /**
     * @param $id
     * @return Sistema
     * @throws \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function findById($id) {
        return parent::findById($id);
    }

    /**
     * @param string $apiKey
     * @return Sistema
     * @throws NotFoundException
     */
    public function findByApiKey($apiKey) {
        $apiKey = (string) $apiKey;
        $rows =  $this->em->createQueryBuilder()
            ->select('s')
            ->from(Sistema::class, 's')
            ->where('s.apiKey = :apiKey')
            ->setParameter('apiKey', $apiKey)
            ->getQuery()
            ->getResult();

        if(count($rows) == 0) {
            throw new NotFoundException(sprintf('sistema con apikey %s no encontrado', $apiKey));
        }

        return $rows[0];
    }

    /**
     * @param $entity
     * @throws \InvalidArgumentException
     * @throws UniqueConstrainException
     */
    public function save($entity) {
        if(!is_object($entity)) {
            throw new \InvalidArgumentException('parametro debe ser de tipo objeto');
        }
        if(!$entity instanceof Sistema) {
            throw new \InvalidArgumentException(sprintf('objeto debe ser de tipo %s, %s recibido como parametro', Sistema::class, get_class($entity)));
        }
        parent::save($entity);
    }
}