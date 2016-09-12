<?php
namespace Principal\Auth\Tests\Controller;

use Principal\Auth\Entity\Collection\RolCollection;
use Principal\Auth\Entity\Rol;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;
use Principal\Auth\Tests\Helper\Silex\ControllerTestCase;
use Principal\Auth\Tests\Helper\Silex\WebTestCase;

/**
 * Class RolControllerTest
 * @package Principal\Auth\Tests\Controller
 * @group functional
 */
class RolControllerTest extends ControllerTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rolRepository;

    public function setUp() {
        parent::setUp();

        $this->rolRepository = $this->getMockBuilder(RepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->app['auth.repository.doctrine.orm.rol'] = $this->rolRepository;
    }

    /**
     * @test
     */
    public function findAllAction_debeRetornarUnJsonConTodosLosRoles() {
        $rol1 = new Rol('rol 1');
        $rol2 = new Rol('rol 2');
        $roles = new RolCollection();
        $roles->add($rol1);
        $roles->add($rol2);

        $this->rolRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($roles));

        $expected = array(
            array(
                'id'     => $rol1->getId(),
                'nombre' => $rol1->getNombre()
            ),
            array(
                'id'     => $rol2->getId(),
                'nombre' => $rol2->getNombre()
            )
        );

        $client = $this->createClient();
        $client->request('GET', '/api/roles?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoUnRolExistente_findByIdAction_DebeRetornarUnJSONConRol() {
        $rol = new Rol('rol test');
        $this->rolRepository
            ->method('findById')
            ->will($this->returnValue($rol));
        $expected = array(
            'id'     => $rol->getId(),
            'nombre' => $rol->getNombre()
        );

        $client = $this->createClient();
        $client->request('GET', '/api/roles/1?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoUnRolNoExistente_findByIdAction_DebeRetornarUn404YMensajeDeError() {
        $this->rolRepository
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));

        $client = $this->createClient();
        $client->request('GET', '/api/roles/1?apikey=test');

        $jsonResponseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals(404, $jsonResponseContent['statusCode']);
        $this->assertArrayHasKey('message', $jsonResponseContent);
        $this->assertArrayHasKey('stackTrace', $jsonResponseContent);
    }

    /**
     * @test
     */
    public function dadoRolNoExistente_newAction_DebeRetornarRolPersistidoYLocation() {
        $this->rolRepository->expects($this->once())
            ->method('save');

        $rol = new Rol('rol test');

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/roles?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => $rol->getNombre()
            ))
        );

        $expected = array(
            'id'     => $rol->getId(),
            'nombre' => $rol->getNombre()
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertContains('/api/roles', $client->getResponse()->headers->get('Location'));
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoDataInvalida_newAction_DebeRetornarBadRequest400YMensajeDeError() {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/roles?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array())
        );

        $jsonResponseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals(400, $jsonResponseContent['statusCode']);
        $this->assertArrayHasKey('message', $jsonResponseContent);
        $this->assertArrayHasKey('stackTrace', $jsonResponseContent);
    }

    /**
     * @test
     */
    public function dadoUnRolExistente_newAction_DebeRetornar409ConflictYMensajeDeError() {
        $this->rolRepository->expects($this->once())
            ->method('save')
            ->will($this->throwException(new UniqueConstrainException()));

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/roles?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => 'rol test'
            ))
        );

        $jsonResponseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(409, $client->getResponse()->getStatusCode());
        $this->assertEquals(409, $jsonResponseContent['statusCode']);
        $this->assertArrayHasKey('message', $jsonResponseContent);
        $this->assertArrayHasKey('stackTrace', $jsonResponseContent);
    }

    /**
     * @test
     */
    public function dadoUnRolExistenteYNoDuplicado_updateAction_DebeActualizarYRetornarUnJsonConRolActualizado() {
        $rol = new Rol('rol test');

        $this->rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($rol));

        $this->rolRepository->expects($this->once())
            ->method('save');

        $expected = array(
            'id'     => $rol->getId(),
            'nombre' => $rol->getNombre()
        );

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/roles/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => $rol->getNombre()
            ))
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoDataInvalida_updateAction_DebeRetornarBadRequestYJsonConError() {
        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/roles/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array())
        );

        $jsonResponseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals(400, $jsonResponseContent['statusCode']);
        $this->assertArrayHasKey('message', $jsonResponseContent);
        $this->assertArrayHasKey('stackTrace', $jsonResponseContent);
    }

    /**
     * @test
     */
    public function dadoUnRolNoExiste_updateAction_DebeRetornarNotFound404YJsonConError() {
        $this->rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/roles/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => 'rol test'
            ))
        );

        $jsonResponseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals(404, $jsonResponseContent['statusCode']);
        $this->assertArrayHasKey('message', $jsonResponseContent);
        $this->assertArrayHasKey('stackTrace', $jsonResponseContent);
    }

    /**
     * @test
     */
    public function dadoUnRolExistenteYDuplicado_updateAction_DebeRetornarConflict404YJsonConError() {
        $rol = new Rol('rol test');

        $this->rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($rol));
        $this->rolRepository->expects($this->once())
            ->method('save')
            ->will($this->throwException(new UniqueConstrainException()));

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/roles/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => $rol->getNombre()
            ))
        );

        $jsonResponseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(409, $client->getResponse()->getStatusCode());
        $this->assertEquals(409, $jsonResponseContent['statusCode']);
        $this->assertArrayHasKey('message', $jsonResponseContent);
        $this->assertArrayHasKey('stackTrace', $jsonResponseContent);
    }
}