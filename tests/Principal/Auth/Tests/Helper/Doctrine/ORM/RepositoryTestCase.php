<?php
namespace Principal\Auth\Tests\Helper\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Principal\Auth\Entity\Accion;
use Principal\Auth\Entity\Modulo;
use Principal\Auth\Entity\Permiso;
use Principal\Auth\Entity\Recurso;
use Principal\Auth\Entity\Rol;
use Principal\Auth\Entity\RolLicitacion;
use Principal\Auth\Entity\Sistema;
use Principal\Auth\Tests\Helper\Silex\WebTestCase;

/**
 * Class RepositoryTestCase
 * @package Principal\Auth\Tests\Helper\Doctrine\ORM
 */
class RepositoryTestCase extends WebTestCase {

    /**
     * @var EntityManager
     */
    protected $em;

    public function setUp() {
        parent::setup();
        $this->em = $this->app['orm.em'];
        $schemaTool = new SchemaTool($this->em);
        $classes = array(
            $this->em->getClassMetadata(Sistema::class),
            $this->em->getClassMetadata(Accion::class),
            $this->em->getClassMetadata(Modulo::class),
            $this->em->getClassMetadata(Recurso::class),
            $this->em->getClassMetadata(Rol::class),
            $this->em->getClassMetadata(Permiso::class),
            $this->em->getClassMetadata(RolLicitacion::class)
        );
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);
    }
}