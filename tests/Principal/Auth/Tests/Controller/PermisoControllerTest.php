<?php
namespace Principal\Auth\Tests\Controller;

use Principal\Auth\Entity\Accion;
use Principal\Auth\Entity\Collection\PermisoCollection;
use Principal\Auth\Entity\Modulo;
use Principal\Auth\Entity\Permiso;
use Principal\Auth\Entity\Recurso;
use Principal\Auth\Entity\Rol;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;
use Principal\Auth\Tests\Helper\Silex\ControllerTestCase;
use Principal\Auth\Tests\Helper\Silex\WebTestCase;

/**
 * Class PermisoControllerTest
 * @package Principal\Auth\Tests\Controller
 */
class PermisoControllerTest extends ControllerTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $permisoRepository;

    public function setUp() {
        parent::setUp();
        $this->permisoRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $this->app['auth.repository.doctrine.orm.permiso'] = $this->permisoRepository;
    }

    /**
     * @param Permiso $permiso
     * @return array
     */
    private function permisoToArray(Permiso $permiso) {
        return array(
            'id'  => $permiso->getId(),
            'rol' => array(
                'id'      => $permiso->getRol()->getId(),
                'nombre'  => $permiso->getRol()->getNombre()
            ),
            'recurso' => array(
                'id'     => $permiso->getRecurso()->getId(),
                'modulo' => array(
                    'id'     => $permiso->getRecurso()->getModulo()->getId(),
                    'nombre' => $permiso->getRecurso()->getModulo()->getNombre()
                ),
                'accion' => array(
                    'id'     => $permiso->getRecurso()->getAccion()->getId(),
                    'nombre' => $permiso->getRecurso()->getAccion()->getNombre()
                )
            )
        );
    }

    /**
     * @test
     */
    public function findAllAction_debeRetornar200YTodosLosRecursosEnJSON() {
        $permiso = new Permiso(
            new Rol('rol test'),
            new Recurso(new Modulo('modulo test'), new Accion('accion test'))
        );
        $permisos = new PermisoCollection();
        $permisos->add($permiso);

        $this->permisoRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($permisos));

        $client = $this->createClient();
        $client->request('GET', '/api/permisos?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(array($this->permisoToArray($permiso)), json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoRegistroExistentePorId_findByIdAction_debeRetornarJSONConRecurso() {
        $permiso = new Permiso(
            new Rol('rol test'),
            new Recurso(
                new Modulo('modulo test'),
                new Accion('accion test')
            )
        );

        $this->permisoRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($permiso));

        $client = $this->createClient();
        $client->request('GET', '/api/permisos/1?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($this->permisoToArray($permiso), json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoRegistroNoExistentePorId_findByIdAction_debeRetornarNotFound404YJSONConError() {
        $this->permisoRepository->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));

        $client = $this->createClient();
        $client->request('GET', '/api/permisos/1?apikey=test');

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 404);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoUnRolYRecursoExistenteYNoRepetido_newAction_debeRetornarUn201LocationYJSONDePermiso() {
        $permiso = new Permiso(
            new Rol('rol test'),
            new Recurso(new Modulo('modulo test'), new Accion('accion test'))
        );

        $rolRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($permiso->getRol()));
        $this->app['auth.repository.doctrine.orm.rol'] = $rolRepository;

        $recursoRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $recursoRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($permiso->getRecurso()));
        $this->app['auth.repository.doctrine.orm.recurso'] = $recursoRepository;

        $this->permisoRepository->expects($this->once())
            ->method('save');

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/permisos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'rol' => array(
                    'id' => 1
                ),
                'recurso' => array(
                    'id' => 1
                )
            ))
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertContains('/api/permisos', $client->getResponse()->headers->get('Location'));
        $this->assertEquals($this->permisoToArray($permiso), json_decode($client->getResponse()->getContent(), true));

    }

    /**
     * @test
     */
    public function dadoUnRolNoExistente_newAction_debeRetornarUn404YJsonDeError() {
        $rolRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));
        $this->app['auth.repository.doctrine.orm.rol'] = $rolRepository;

        $client = $this->createClient();
        $client->request( 'POST',
            '/api/permisos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'rol' => array(
                    'id' => 1
                ),
                'recurso' => array(
                    'id' => 1
                )
            )));

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 404);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoUnRecursoNoExistente_newAction_debeRetornarUn404YJsonDeError() {
        $rolRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(new Rol('test')));

        $this->app['auth.repository.doctrine.orm.rol'] = $rolRepository;

        $recursoRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $recursoRepository->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));
        $this->app['auth.repository.doctrine.orm.recurso'] = $recursoRepository;

        $client = $this->createClient();
        $client->request( 'POST',
            '/api/permisos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'rol' => array(
                    'id' => 1
                ),
                'recurso' => array(
                    'id' => 1
                )
            )));

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 404);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoUnPermisoExistente_newAction_debeRetornarUn409ConflictYJsonDeError() {
        $permiso = new Permiso(
            new Rol('rol test'),
            new Recurso(new Modulo('modulo test'), new Accion('accion test'))
        );

        $rolRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($permiso->getRol()));
        $this->app['auth.repository.doctrine.orm.rol'] = $rolRepository;

        $recursoRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $recursoRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($permiso->getRecurso()));
        $this->app['auth.repository.doctrine.orm.recurso'] = $recursoRepository;

        $this->permisoRepository->expects($this->once())
            ->method('save')
            ->will($this->throwException(new UniqueConstrainException()));

        $client = $this->createClient();
        $client->request( 'POST',
            '/api/permisos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'rol' => array(
                    'id' => 1
                ),
                'recurso' => array(
                    'id' => 1
                )
            )));

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(409, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 409);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoDataInvalida_newAction_debeRetornarUn400BadRequesetYJsonDeError() {
        $client = $this->createClient();
        $client->request( 'POST',
            '/api/permisos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array())
        );

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 400);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }
}