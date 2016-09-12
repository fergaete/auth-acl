<?php
namespace Principal\Auth\Tests\Repository\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Principal\Auth\Entity\Accion;
use Principal\Auth\Entity\Collection\RolLicitacionCollection;
use Principal\Auth\Entity\Modulo;
use Principal\Auth\Entity\Permiso;
use Principal\Auth\Entity\Recurso;
use Principal\Auth\Entity\Rol;
use Principal\Auth\Entity\RolLicitacion;
use Principal\Auth\Repository\Doctrine\ORM\RolLicitacionRepository;
use Principal\Auth\Tests\Helper\Doctrine\ORM\RepositoryTestCase;

/**
 * Class RolLicitacionRepositoryTest
 * @package Principal\Auth\Tests\Repository\Doctrine\ORM
 */
class RolLicitacionRepositoryTest extends RepositoryTestCase {

    /**
     * @var RolLicitacionRepository
     */
    private $rolLicitacionRepository;

    public function setUp() {
        parent::setUp();
        $this->rolLicitacionRepository = $this->app['auth.repository.doctrine.orm.rol_licitacion'];
    }

    /**
     * @test
     */
    public function findAll_debeRetornarTodasLasEntidades() {
        $rolLicitacion = new RolLicitacion(new Rol('rol test'), 1, 1);
        $this->rolLicitacionRepository->save($rolLicitacion);

        $rolLicitacionCollection = new RolLicitacionCollection();
        $rolLicitacionCollection->add($rolLicitacion);


        $this->assertEquals($rolLicitacionCollection, $this->rolLicitacionRepository->findAll(array()));
    }


    /**
     * @test
     */
    public function findAll_conSortingPorNombreRolDescendente_debeRetornarTodasLasEntidadesOrdenadas() {
        $rolLicitacion1 = new RolLicitacion(new Rol('rol test1'), 1, 1);
        $rolLicitacion2 = new RolLicitacion(new Rol('rol test2'), 1, 1);
        $rolLicitacionCollection = new RolLicitacionCollection(
            array($rolLicitacion2, $rolLicitacion1)
        );

        $this->rolLicitacionRepository->save($rolLicitacion1);
        $this->rolLicitacionRepository->save($rolLicitacion2);

        $this->assertEquals($rolLicitacionCollection, $this->rolLicitacionRepository->findAll(array(), array('rol.nombre' => 'DESC')));
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function dadoUnRegistroNoExistentePorId_findById_debeRetornarException() {
        $this->rolLicitacionRepository->findById(1);
    }

    /**
     * @test
     */
    public function dadoUnRegistroExistentePorId_findById_debeRetornarEntidad() {
        $rolLicitacion = new RolLicitacion(new Rol('rol test'), 1 , 1);
        $this->rolLicitacionRepository->save($rolLicitacion);
        $this->assertEquals($rolLicitacion, $this->rolLicitacionRepository->findById($rolLicitacion->getId()));
    }

    /**
     * @test
     */
    public function dadoUnRegistroExistentePorIdLicitacion_findByIdLicitacion_debeRetornarEntidades() {
        $idLicitacion = 1;
        $idUsuario = 1;

        $rolLicitacion1 = new RolLicitacion(new Rol('rol test1'), $idLicitacion , $idUsuario);
        $rolLicitacion2 = new RolLicitacion(new Rol('rol test2'), $idLicitacion , $idUsuario);
        $rolLicicationCollection = new RolLicitacionCollection(array($rolLicitacion1, $rolLicitacion2));

        $this->rolLicitacionRepository->save($rolLicitacion1);
        $this->rolLicitacionRepository->save($rolLicitacion2);

        $this->assertEquals($rolLicicationCollection, $this->rolLicitacionRepository->findAll(array('this.idLicitacion' => $idLicitacion)));
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\UniqueConstrainException
     */
    public function dadoUnRegistroDuplicado_save_debeLanzarException() {
        $rolLicitacion1 = new RolLicitacion(new Rol('rol test'), 1 , 1);
        $this->rolLicitacionRepository->save($rolLicitacion1);

        $rolLicitacion2 = new RolLicitacion(new Rol('rol test'), 1 , 1);
        $this->rolLicitacionRepository->save($rolLicitacion2);
    }

    /**
     * @test
     */
    public function dadoUnUsuarioDeUnaLicitacionConPermisosParaEseRecurso_findByIdLicitacionIdUsuarioIdModuloIdAccion_debeRetornarEntidad() {
        /**
         * @var EntityManager $em
         */
        $em = $this->app['orm.em'];
        $recurso = new Recurso(new Modulo('modulo 1'), new Accion('accion 1'));
        $rol = new Rol('rol');
        $permiso = new Permiso($rol, $recurso);
        $em->persist($permiso);
        $em->flush();

        $rolLicitacion = new RolLicitacion($rol, 1, 1);

        $this->rolLicitacionRepository->save($rolLicitacion);

        $rolLicitacionCollection = new RolLicitacionCollection();
        $rolLicitacionCollection->add($rolLicitacion);


        $this->assertEquals($rolLicitacionCollection, $this->rolLicitacionRepository->findByIdLicitacionAndIdUsuarioAndRecurso(1, 1, $recurso));
    }

    /**
     * @test
     */
    public function dadoUnUsuarioDeUnaLicitacionSinPermisosParaEseRecurso_findByIdLicitacionIdUsuarioIdModuloIdAccion_debeRetornarCollectionVacia() {
        /**
         * @var EntityManager $em
         */
        $em = $this->app['orm.em'];
        $recurso = new Recurso(new Modulo('modulo 1'), new Accion('accion 1'));
        $rol = new Rol('rol');
        $permiso = new Permiso($rol, $recurso);
        $em->persist($permiso);
        $em->flush();

        $rolLicitacion = new RolLicitacion($rol, 1, 1);

        $this->rolLicitacionRepository->save($rolLicitacion);

        $recurso->getModulo()->setNombre('test');
        $this->assertEquals(0, $this->rolLicitacionRepository->findByIdLicitacionAndIdUsuarioAndRecurso(1, 1, $recurso)->count());
    }
}