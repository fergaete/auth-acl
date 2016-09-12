<?php
namespace Principal\Auth\Tests\Repository\Doctrine\ORM\Event;

use Principal\Auth\Entity\Accion;
use Principal\Auth\Tests\Helper\Doctrine\ORM\RepositoryTestCase;

/**
 * Class TimestampableCreatedUpdatedEventTest
 * @package Principal\Auth\Tests\Repository\Doctrine\ORM\Event
 */
class TimestampableCreatedUpdatedEventTest extends RepositoryTestCase {

    /**
     * @test
     */
    public function alInsertarUnaNuevaEntidad_prePersist_DebeSetearCreatedAt() {
        $accion = new Accion('test');
        $em = $this->app['orm.em'];
        $em->persist($accion);
        $em->flush();

        $this->assertTrue($accion->getCreatedAt() instanceof \DateTime);
        $this->assertNull($accion->getUpdatedAt());
    }

    /**
     * @test
     */
    public function alActualizarUnaEntidadEnBd_preUpdate_debeSetearUpdatedAt() {
        $accion = new Accion('test');
        $em = $this->app['orm.em'];
        $em->persist($accion);
        $em->flush();

        $accion->setNombre('test 1');
        $em->persist($accion);
        $em->flush();

        $this->assertTrue($accion->getCreatedAt() instanceof \DateTime);
        $this->assertTrue($accion->getUpdatedAt() instanceof \DateTime);
    }
}