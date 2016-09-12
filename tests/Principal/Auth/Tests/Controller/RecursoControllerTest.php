<?php
namespace Principal\Auth\Tests\Controller;

use Principal\Auth\Entity\Accion;
use Principal\Auth\Entity\Collection\RecursoCollection;
use Principal\Auth\Entity\Modulo;
use Principal\Auth\Entity\Recurso;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;
use Principal\Auth\Tests\Helper\Silex\ControllerTestCase;
use Principal\Auth\Tests\Helper\Silex\WebTestCase;

/**
 * Class RecursoControllerTest
 * @package Principal\Auth\Tests\Controller
 */
class RecursoControllerTest extends ControllerTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $recursoRepository;

    public function setUp() {
        parent::setUp();
        $this->recursoRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $this->app['auth.repository.doctrine.orm.recurso'] = $this->recursoRepository;
    }

    /**
     * @test
     */
    public function findAllAction_debeRetornar200YTodosLosRecursosEnJSON() {
        $recurso = new Recurso(new Modulo('modulo'),  new Accion('accion'));
        $recursos = new RecursoCollection();
        $recursos->add($recurso);

        $this->recursoRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($recursos));

        $expected = array(
            array(
                'id'     => $recurso->getId(),
                'accion' => array(
                    'id'     => $recurso->getAccion()->getId(),
                    'nombre' => $recurso->getAccion()->getNombre()
                ),
                'modulo' => array(
                    'id'     => $recurso->getModulo()->getId(),
                    'nombre' => $recurso->getModulo()->getNombre()
                )
            )
        );

        $this->recursoRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($recursos));

        $client = $this->createClient();
        $client->request('GET', '/api/recursos?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoUnaRecursoExistente_findByIdAction_debeRetornarUn200YJSON() {
        $recurso = new Recurso(new Modulo('modulo test'), new Accion('accion test'));
        $this->recursoRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($recurso));

        $expected = array(
            'id'     => $recurso->getId(),
            'accion' => array(
                'id'     => $recurso->getAccion()->getId(),
                'nombre' => $recurso->getAccion()->getNombre()
            ),
            'modulo' => array(
                'id'     => $recurso->getModulo()->getId(),
                'nombre' => $recurso->getModulo()->getNombre()
            )
        );

        $client = $this->createClient();
        $client->request('GET', '/api/recursos/1?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoUnaRecursoNoExistente_findByIdAction_debeRetornarNotFound404() {
        $this->recursoRepository->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));

        $client = $this->createClient();
        $client->request('GET', '/api/recursos/1?apikey=test');

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 404);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoUnModuloNoExistente_newAction_debeRetornarUn404YJSONDeError() {
        $moduloRepositoryMock = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $moduloRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));
        $this->app['auth.repository.doctrine.orm.modulo'] = $moduloRepositoryMock;

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/recursos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'modulo' => array(
                    'id' => 1
                ),
                'accion' => array(
                    'id' => 1
                )
            ))
        );

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 404);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoUnModuloExistenteYAccionNoExistente_newAction_debeRetornarUn404YJSONDeError() {
        $moduloRepositoryMock = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $moduloRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(new Modulo('modulo test')));
        $this->app['auth.repository.doctrine.orm.modulo'] = $moduloRepositoryMock;

        $accionRepositoryMock = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $accionRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));
        $this->app['auth.repository.doctrine.orm.accion'] = $accionRepositoryMock;

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/recursos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'modulo' => array(
                    'id' => 1
                ),
                'accion' => array(
                    'id' => 1
                )
            ))
        );

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 404);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoUnRecursoYaExistente_newAction_debeRetornarConflict409YJSONDeError() {
        $moduloRepositoryMock = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $moduloRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(new Modulo('modulo test')));
        $this->app['auth.repository.doctrine.orm.modulo'] = $moduloRepositoryMock;

        $accionRepositoryMock = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $accionRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(new Accion('accion test')));
        $this->app['auth.repository.doctrine.orm.accion'] = $accionRepositoryMock;

        $this->recursoRepository->expects($this->once())
            ->method('save')
            ->will($this->throwException(new UniqueConstrainException()));

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/recursos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'modulo' => array(
                    'id' => 1
                ),
                'accion' => array(
                    'id' => 1
                )
            ))
        );

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(409, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 409);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoUnModuloExistenteYAccionExistente_newAction_debeRetornarUn201YJSON() {
        $modulo =  new Modulo('modulo test');
        $moduloRepositoryMock = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $moduloRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($modulo));
        $this->app['auth.repository.doctrine.orm.modulo'] = $moduloRepositoryMock;

        $accion = new Accion('accion test');
        $accionRepositoryMock = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $accionRepositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($accion));
        $this->app['auth.repository.doctrine.orm.accion'] = $accionRepositoryMock;

        $this->recursoRepository->expects($this->once())->method('save');

        $recurso = new Recurso($modulo, $accion);
        $expected = array(
            'id'     => $recurso->getId(),
            'modulo' => array(
                'id'     => $recurso->getModulo()->getId(),
                'nombre' => $recurso->getModulo()->getNombre()
            ),
            'accion' => array(
                'id'     => $recurso->getAccion()->getId(),
                'nombre' => $recurso->getAccion()->getNombre()
            )
        );

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/recursos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'modulo' => array(
                    'id' => 1
                ),
                'accion' => array(
                    'id' => 1
                )
            ))
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->stringContains('/api/recursos', $client->getResponse()->headers->get('Location'));
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoDataInvalida_newAction_debeRetornar400BadRequest() {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/recursos?apikey=test',
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