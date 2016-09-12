<?php
namespace Principal\Auth\Tests\Repository\Doctrine\ORM;

use Principal\Auth\Entity\Collection\ModuloCollection;
use Principal\Auth\Entity\Modulo;
use Principal\Auth\Repository\Doctrine\ORM\ModuloRepository;
use Principal\Auth\Tests\Helper\Doctrine\ORM\RepositoryTestCase;

/**
 * Class ModuloRepositoryTest
 * @package Principal\Auth\Tests\Repository\Doctrine\ORM
 */
class ModuloRepositoryTest extends RepositoryTestCase {

    /**
     * @var ModuloRepository
     */
    private $moduloRepository;

    public function setUp() {
        parent::setUp();
        $this->moduloRepository = $this->app['auth.repository.doctrine.orm.modulo'];
    }

    /**
     * @test
     */
    public function findAll_debeRetornarCollectionDeModulos() {
        $modulo1 = new Modulo('modulo test 1');
        $modulo2 = new Modulo('modulo test 2');

        $this->moduloRepository->save($modulo1);
        $this->moduloRepository->save($modulo2);

        $modulos = new ModuloCollection();
        $modulos->add($modulo1);
        $modulos->add($modulo2);

        $this->assertEquals($modulos, $this->moduloRepository->findAll(array()));
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function dadoUnRegistroNoExistente_findById_DebeLanzarException() {
        $this->moduloRepository->findById(1);
    }

    /**
     * @test
     */
    public function dadoUnRegistroExistente_findById_DebeRetornarEntidad() {
        $modulo = new Modulo('modulo test');
        $this->moduloRepository->save($modulo);

        $this->assertEquals($modulo ,$this->moduloRepository->findById(1));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function dadoUnaEntidadQueNoEsDeTipoAccion_save_debeLanzarException() {
        $this->moduloRepository->save(array());
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\UniqueConstrainException
     */
    public function dadoDosRegistrosConUnMismoNombre_unitOfWork_debeLanzarException() {
        $this->moduloRepository->save(new Modulo('modulo test'));
        $this->moduloRepository->save(new Modulo('modulo test'));
    }
}