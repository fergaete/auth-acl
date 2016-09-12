<?php
namespace Principal\Auth\Tests\Repository\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Principal\Auth\Entity\Accion;
use Principal\Auth\Entity\Collection\PermisoCollection;
use Principal\Auth\Entity\Modulo;
use Principal\Auth\Entity\Permiso;
use Principal\Auth\Entity\Recurso;
use Principal\Auth\Entity\Rol;
use Principal\Auth\Repository\Doctrine\ORM\PermisoRepository;
use Principal\Auth\Tests\Helper\Doctrine\ORM\RepositoryTestCase;

/**
 * Class PermisoRepositoryTest
 * @package Principal\Auth\Tests\Repository\Doctrine\ORM
 */
class PermisoRepositoryTest extends RepositoryTestCase {

    /**
     * @var PermisoRepository
     */
    private $permisoRepository;

    public function setUp() {
        parent::setUp();
        $this->permisoRepository = $this->app['auth.repository.doctrine.orm.permiso'];
    }


    public function persistPermiso(Permiso $permiso) {
        /**
         * @var EntityManager $em
         */
        $em = $this->app['orm.em'];

        $em->persist($permiso->getRol());
        $em->persist($permiso->getRecurso()->getModulo());
        $em->persist($permiso->getRecurso()->getAccion());
        $em->persist($permiso->getRecurso());
        $this->permisoRepository->save($permiso);
        $em->flush();
    }

    /**
     * @test
     */
    public function findAllDebeRetornarCollectionDeEntidades() {
        $permiso = new Permiso(
            new Rol('rol test'),
            new Recurso(
                new Modulo('modulo test'),
                new Accion('accion test')
            )
        );

        $this->persistPermiso($permiso);

        $permisos = new PermisoCollection();
        $permisos->add($permiso);

        $this->assertEquals($permisos, $this->permisoRepository->findAll(array()));
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function dadoRegistroNoExistePorId_FindById_DebeLanzarException(){
        $this->permisoRepository->findById(1);
    }

    /**
     * @test
     */
    public function dadoUnRegistroExistentePorId_findById_debeRetornarEntidad() {
        $permiso = new Permiso(
            new Rol('rol test'),
            new Recurso(
                new Modulo('modulo test'),
                new Accion('accion test')
            )
        );

        $this->persistPermiso($permiso);

        $this->assertEquals($permiso, $this->permisoRepository->findById($permiso->getId()));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function dadoUnParametroInvalido_save_debeLanzarException() {
        $this->permisoRepository->save(array());
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\UniqueConstrainException
     */
    public function dadoUnPermisoDuplicado_save_debeLanzarException() {
        $permiso1 = new Permiso(
            new Rol('rol test'),
            new Recurso(
                new Modulo('modulo test'),
                new Accion('accion test')
            )
        );
        $permiso2 = new Permiso(
            new Rol('rol test'),
            new Recurso(
                new Modulo('modulo test'),
                new Accion('accion test')
            )
        );

        $this->persistPermiso($permiso1);
        $this->persistPermiso($permiso2);
    }
}