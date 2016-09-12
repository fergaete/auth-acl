<?php
namespace Principal\Auth\Tests\Repository\Doctrine\ORM;

use Principal\Auth\Entity\Collection\RolCollection;
use Principal\Auth\Entity\Rol;
use Principal\Auth\Repository\Doctrine\ORM\RolRepository;
use Principal\Auth\Tests\Helper\Doctrine\ORM\RepositoryTestCase;

/**
 * Class RolRepositoryTest
 * @package Principal\Auth\Tests\Repository\Doctrine\ORM
 */
class RolRepositoryTest extends RepositoryTestCase {

    /**
     * @var RolRepository
     */
    private $rolRepository;

    public function setUp() {
        parent::setUp();
        $this->rolRepository = $this->app['auth.repository.doctrine.orm.rol'];
    }

    /**
     * @test
     */
    public function findAllDebeRetornarCollectionDeEntidades() {
        $rol1 = new Rol('rol 1');
        $rol2 = new Rol('rol 2');
        $this->rolRepository->save($rol1);
        $this->rolRepository->save($rol2);

        $roles  = new RolCollection();
        $roles->add($rol1);
        $roles->add($rol2);

        $this->assertEquals($roles, $this->rolRepository->findAll(array()));
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function dadoRegistroNoExistentePorId_findById_debeLanzarException() {
        $this->rolRepository->findById(1);
    }

    /**
     * @test
     */
    public function DadoUnRegistroExisten_findById_DebeRetornarEntidad() {
        $rol = new Rol('rol test');
        $this->rolRepository->save($rol);

        $this->assertEquals($rol, $this->rolRepository->findById(1));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function dadoUnEntidadQueNoEsDeTipoAccion_save_debeLanzarException() {
        $this->rolRepository->save(array());
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\UniqueConstrainException
     */
    public function dadoDosRegistrosConUnMismoNombre_save_debeLanzarException() {
        $rol1 = new Rol('rol test');
        $rol2 = new Rol('rol test');

        $this->rolRepository->save($rol1);
        $this->rolRepository->save($rol2);
    }
}