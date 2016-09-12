<?php
namespace Principal\Auth\Tests\Controller;

use Principal\Auth\Entity\Accion;
use Principal\Auth\Entity\Collection\AccionCollection;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;
use Principal\Auth\Tests\Helper\Silex\ControllerTestCase;
use Principal\Auth\Tests\Helper\Silex\WebTestCase;

/**
 * Class AccionControllerTest
 * @package Principal\Auth\Tests\Controller
 * @group functional
 */
class AccionControllerTest extends ControllerTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $accionRepositoryMock;

    public function setUp() {
        parent::setUp();
        $this->accionRepositoryMock = $this->getMockBuilder(RepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->app['auth.repository.doctrine.orm.accion'] = $this->accionRepositoryMock;
    }

    /**
     * @test
     */
    public function findAllAction_DebeRetornar200YTodosLasAccionesEnJSON() {
        $acciones = new AccionCollection();
        $acciones->add(new Accion('accion 1'));
        $acciones->add(new Accion('accion 2'));

        $this->accionRepositoryMock
            ->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($acciones));

        $expected = array(
            array(
                'id'      => null,
                'nombre'  => 'accion 1'
            ),
            array(
                'id'      => null,
                'nombre'  => 'accion 2'
            )
        );

        $client = $this->createClient();
        $client->request('GET', '/api/acciones?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($expected,json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoUnaAccionNoExistente_findByIdAction_debeRetornarNotFound404() {
        $this->accionRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));

        $client = $this->createClient();
        $client->request('GET', '/api/acciones/1?apikey=test');

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 404);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoUnaAccionExistente_findByIdAction_debeRetornarUn200YJSON() {
        $accion = new Accion('accion test');
        $this->accionRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($accion));

        $expected = array(
            'id'      => null,
            'nombre'  => 'accion test'
        );

        $client = $this->createClient();
        $client->request('GET', '/api/acciones/1?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoUnPostDeUnaAccionQueNoExiste_newAction_debePersistirDataYRetornarUn200YEnviarElIdEnLocation() {
        $accion = new Accion('accion test');

        $this->accionRepositoryMock->expects($this->once())
            ->method('save');

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/acciones?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => 'accion test'
            ))
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertContains("/api/accion/", $client->getResponse()->headers->get("Location"));
        $this->assertEquals($accion->getNombre(),json_decode($client->getResponse()->getContent(), true)['nombre']);

    }

    /**
     * @test
     */
    public function dadoUnPostDeUnaAccionExistente_SeDebeLanzarUnError409ConflictEIndicarMensaje() {
        $this->accionRepositoryMock->expects($this->once())
            ->method('save')
            ->will($this->throwException(new UniqueConstrainException()));

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/acciones?apikey=test',
            array(),
            array(),
            array('CONTENT-TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => 'accion test'
            ))
        );

        $jsonResponseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(409, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponseContent['statusCode'], 409);
        $this->assertArrayHasKey('message', $jsonResponseContent);
        $this->assertArrayHasKey('stackTrace', $jsonResponseContent);
    }

    /**
     * @test
     */
    public function dadoUnPutDeUnaAccionExistente_updateAction_debeActualizarRegistroYRetornar200YEnviarJSON() {
        $this->accionRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(new Accion('accion test')));

        $this->accionRepositoryMock->expects($this->once())
            ->method('save');

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/acciones/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => 'accion test'
            ))
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('accion test', json_decode($client->getResponse()->getContent())->nombre);
    }

    /**
     * @test
     */
    public function dadoUnPutDeUnaAccionQueNoExiste_updateAction_debeRetornar404YErrorEnJSON() {
        $this->accionRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(new Accion('accion test')));

        $this->accionRepositoryMock->expects($this->once())
            ->method('save')
            ->will($this->throwException(new NotFoundException()));

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/acciones/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array('nombre' => 'accion test'))
        );

        $jsonResponse = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals(404, $jsonResponse['statusCode']);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoUnPutDeUnaAccionConMismoNombre_update_Action_debeRetornar409YErrorEnJson() {
        $this->accionRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(new Accion('accion test')));

        $this->accionRepositoryMock->expects($this->once())
            ->method('save')
            ->will($this->throwException(new UniqueConstrainException()));

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/acciones/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array('nombre' => 'accion test'))
        );

        $jsonResponse = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(409, $client->getResponse()->getStatusCode());
        $this->assertEquals(409, $jsonResponse['statusCode']);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }
}