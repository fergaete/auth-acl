<?php
namespace Principal\Auth\Tests\Repository\Doctrine\ORM;

use Principal\Auth\Entity\Accion;
use Principal\Auth\Entity\Collection\RecursoCollection;
use Principal\Auth\Entity\Modulo;
use Principal\Auth\Entity\Recurso;
use Principal\Auth\Repository\Doctrine\ORM\RecursoRepository;
use Principal\Auth\Tests\Helper\Doctrine\ORM\RepositoryTestCase;

/**
 * Class RecursoRepositoryTest
 * @package Principal\Auth\Tests\Repository\Doctrine\ORM
 */
class RecursoRepositoryTest extends RepositoryTestCase {

    /**
     * @var RecursoRepository
     */
    private $recursoRepository;

    public function setUp() {
        parent::setUp();
        $this->recursoRepository = $this->app['auth.repository.doctrine.orm.recurso'];
    }

    /**
     * @test
     */
    public function findAll_debeRetornarCollectionDeRecursos() {
        $accion = new Accion('accion test');
        $this->em->persist($accion);

        $modulo = new Modulo('modulo test');
        $this->em->persist($modulo);

        $recurso = new Recurso($modulo, $accion);
        $this->recursoRepository->save($recurso);

        $recursos = new RecursoCollection();
        $recursos->add($recurso);

        $this->assertEquals($recursos, $this->recursoRepository->findAll(array()));
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\NotFoundException
     */
    public function dadoRegistroNoExistentePorId_findById_debeLanzarException() {
        $this->recursoRepository->findById(1);
    }

    /**
     * @test
     */
    public function dadoRegistroExistentePorId_findById_debeLanzarException() {
        $accion = new Accion('accion test');
        $this->em->persist($accion);

        $modulo = new Modulo('modulo test');
        $this->em->persist($modulo);

        $recurso = new Recurso($modulo, $accion);
        $this->recursoRepository->save($recurso);

        $this->assertEquals($recurso, $this->recursoRepository->findById(1));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function dadoUnParametroInvalido_save_DebeLanzarException() {
        $this->recursoRepository->save(array());
    }

    /**
     * @test
     * @expectedException \Principal\Auth\Repository\Exception\UniqueConstrainException
     */
    public function dadoRecursoDuplicado_unitOfWorkDebeLanzarException() {
        $accion = new Accion('accion test');
        $this->em->persist($accion);

        $modulo = new Modulo('modulo test');
        $this->em->persist($modulo);

        $recurso1 = new Recurso($modulo, $accion);
        $this->recursoRepository->save($recurso1);

        $recurso2 = new Recurso($modulo, $accion);
        $this->recursoRepository->save($recurso2);
    }
}