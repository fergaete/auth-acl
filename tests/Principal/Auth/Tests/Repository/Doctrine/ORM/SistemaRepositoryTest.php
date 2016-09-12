<?php
namespace Principal\Auth\Tests\Repository\Doctrine\ORM;

use Principal\Auth\Entity\Collection\SistemaCollection;
use Principal\Auth\Entity\Sistema;
use Principal\Auth\Repository\Doctrine\ORM\SistemaRepository;
use Principal\Auth\Tests\Helper\Doctrine\ORM\RepositoryTestCase;

/**
 * Class SistemaRepositoryTest
 * @package Principal\Auth\Tests\Repository\Doctrine\ORM
 */
class SistemaRepositoryTest extends RepositoryTestCase {
    /**
     * @var SistemaRepository
     */
    private $sistemaRepository;

    public function setUp() {
        parent::setup();
        $this->sistemaRepository = $this->app['auth.repository.doctrine.orm.sistema'];
    }

    /**
     * @test
     */
    public function findAllDebeRetornarCollectionDeEntidades() {
        $sistema1 = new Sistema('sistema 1', 'apikey 1');
        $sistema2 = new Sistema('sistema 2', 'apikey 2');

        $acciones = new SistemaCollection();
        $acciones->add($sistema1);
        $acciones->add($sistema2);


        $this->sistemaRepository->save($sistema1);
        $this->sistemaRepository->save($sistema2);

        $this->assertEquals($acciones, $this->sistemaRepository->findAll(array()));
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function dadoRegistroNoExistentePorId_findById_debeLanzarException() {
        $this->sistemaRepository->findById(1);
    }

    /**
     * @test
     */
    public function dadoUnRegistroExistentePorId_findById_debeRetornarEntidad() {
        $accion = new Sistema('sistema 1', 'apikey 1');
        $this->sistemaRepository->save($accion);
        $this->assertEquals($accion, $this->sistemaRepository->findById($accion->getId()));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function dadoUnEntidadQueNoEsDeTipoAccion_save_debeLanzarException() {
        $this->sistemaRepository->save(array());
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\UniqueConstrainException
     */
    public function dadoDosRegistrosConUnMismoNombre_save_debeLanzarException() {
        $sistema1 = new Sistema('sistema 1', 'apikey 1');
        $sistema2 = new Sistema('sistema 1', 'apikey 1');

        $this->sistemaRepository->save($sistema1);
        $this->sistemaRepository->save($sistema2);
    }

    /**
     * @test
     */
    public function dadoUnRegistroExistentePorApiKey_findByApiKey_debeRetornarEntidad() {
        $sistema1 = new Sistema('sistema 1', 'apikey 1');
        $sistema2 = new Sistema('sistema 2', 'apikey 2');

        $this->sistemaRepository->save($sistema1);
        $this->sistemaRepository->save($sistema2);

        $this->assertEquals($sistema1, $this->sistemaRepository->findByApiKey('apikey 1'));
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function dadoUnRegistroNoExistentePorApiKey_findByApiKey_debeLanzarException() {
        $this->sistemaRepository->findByApiKey('apikey 1');
    }
}