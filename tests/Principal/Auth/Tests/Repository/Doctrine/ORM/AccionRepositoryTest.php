<?php
namespace Principal\Auth\Tests\Repository\Doctrine\ORM;

use Principal\Auth\Tests\Helper\Doctrine\ORM\RepositoryTestCase;
use Principal\Auth\Entity\Collection\AccionCollection;
use Principal\Auth\Repository\Doctrine\ORM\AccionRepository;
use Principal\Auth\Entity\Accion;

/**
 * Class AccionRepositoryTest
 * @package Principal\Auth\Tests\Repository\Doctrine\ORM
 * @group functional
 */
class AccionRepositoryTest extends RepositoryTestCase {

    /**
     * @var AccionRepository
     */
    private $accionRepository;

    public function setUp() {
        parent::setup();
        $this->accionRepository = $this->app['auth.repository.doctrine.orm.accion'];
    }

    /**
     * @test
     */
    public function findAllDebeRetornarCollectionDeEntidades() {
        $accion1 = new Accion('accion test 1');
        $accion2 = new Accion('accion test 2');

        $acciones = new AccionCollection();
        $acciones->add($accion1);
        $acciones->add($accion2);

        $this->accionRepository->save($accion1);
        $this->accionRepository->save($accion2);

        $this->assertEquals($acciones, $this->accionRepository->findAll(array()));
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function dadoRegistroNoExistentePorId_findById_debeLanzarException() {
        $this->accionRepository->findById(1);
    }

    /**
     * @test
     */
    public function dadoUnRegistroExistentePorId_findById_debeRetornarEntidad() {
        $accion = new Accion('accion test');
        $this->accionRepository->save($accion);
        $this->assertEquals($accion, $this->accionRepository->findById(1));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function dadoUnEntidadQueNoEsDeTipoAccion_save_debeLanzarException() {
        $this->accionRepository->save(array());
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\UniqueConstrainException
     */
    public function dadoDosRegistrosConUnMismoNombre_save_debeLanzarException() {
        $accion1 = new Accion('accion test');
        $accion2 = new Accion('accion test');

        $this->accionRepository->save($accion1);
        $this->accionRepository->save($accion2);
    }
}