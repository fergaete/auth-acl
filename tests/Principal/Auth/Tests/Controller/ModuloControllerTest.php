<?php
namespace Principal\Auth\Tests\Controller;

use Principal\Auth\Entity\Collection\ModuloCollection;
use Principal\Auth\Entity\Modulo;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Tests\Helper\Silex\ControllerTestCase;
use Principal\Auth\Tests\Helper\Silex\WebTestCase;
use Principal\Auth\Repository\RepositoryInterface;

/**
 * @group functional
 */
class ModuloControllerTest extends ControllerTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    public function setUp() {
        parent::setUp();
        $this->repositoryMock = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $this->app['auth.repository.doctrine.orm.modulo'] = $this->repositoryMock;
    }

    /**
     * @test
     */
    public function findAll_DebeRetornar200YJsonConTodosLosModulos() {
        $modulo1 = new Modulo('modulo 1');
        $modulo2 = new Modulo('modulo 2');
        $modulos = new ModuloCollection();
        $modulos->add($modulo1);
        $modulos->add($modulo2);

        $this->repositoryMock->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($modulos));

        $expected = array(
            array(
                'id'     => $modulo1->getId(),
                'nombre' => $modulo1->getNombre()
            ),
            array(
                'id'     => $modulo2->getId(),
                'nombre' => $modulo2->getNombre()
            ));
        $client = $this->createClient();
        $client->request('GET', '/api/modulos?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoUnModuloNoExistente_findByIdAction_debeRetornarNotFound404() {
        $this->repositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));

        $client = $this->createClient();
        $client->request('GET', '/api/modulos/1?apikey=test');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($jsonResponse['statusCode'], 404);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoUnModuloExistente_findByIdAction_debeRetornarUn200YJSONConModulo() {
        $modulo = new Modulo('modulo test');
        $this->repositoryMock
            ->method('findById')
            ->will($this->returnValue($modulo));

        $expected = array(
            'id'     => $modulo->getId(),
            'nombre' => $modulo->getNombre()
        );
        $client = $this->createClient();
        $client->request('GET', '/api/modulos/1?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($expected, json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoUnPostConDataInvalida_newAction_debeRetornarBadRequest400YJsonConError() {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/modulos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array())
        );

        $jsonResponseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponseContent['statusCode'], 400);
        $this->assertArrayHasKey('message', $jsonResponseContent);
        $this->assertArrayHasKey('stackTrace', $jsonResponseContent);
    }

    /**
     * @test
     */
    public function dadoUnPostDeUnModuloQueNoExiste_newAction_debePersistirDataYRetornarUn201ConElIdEnLocationDelRecursoYRetornarObjetoPersistido() {
        $modulo = new Modulo('modulo test');

        $this->repositoryMock->expects($this->once())
            ->method('save');

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/modulos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => $modulo->getNombre()
            ))
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertContains("/api/modulos/", $client->getResponse()->headers->get("Location"));
        $this->assertEquals($modulo->getNombre(), json_decode($client->getResponse()->getContent())->nombre);
    }

    /**
     * @test
     */
    public function dadoUnPostDeUnModuloExistente_newAction_SeDebeRetornarUnError409ConflictYObjetoError() {
        $modulo = new Modulo('modulo test');
        $this->repositoryMock->expects($this->once())
            ->method('save')
            ->will($this->throwException(new UniqueConstrainException()));

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/modulos?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => $modulo->getNombre()
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
    public function dadoUnPutDeUnModuloExistente_updateAction_debeActualizarRegistroYRetornar200YEnviarJSON() {
        $modulo = new Modulo('modulo test');

        $this->repositoryMock->method('findById')
            ->will($this->returnValue($modulo));

        $this->repositoryMock->expects($this->once())
            ->method('save');

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/modulos/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => $modulo->getNombre()
            ))
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->stringContains('/api/modulos', $client->getResponse()->headers->get('Location'));
        $this->assertEquals($modulo->getNombre(), json_decode($client->getResponse()->getContent())->nombre);
    }

    /**
     * @test
     */
    public function dadoUnPutDeUnModuloNoExistente_updateAction_debeRetornar404NotFoundYObjetoError() {
        $this->repositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/modulos/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => 'modulo test'
            ))
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
    public function dadoUnPutDeUnModuloDuplicado_updateAction_debeRetornar409ConflictYRetornarErrorEnJSON() {
        $modulo = new Modulo('modulo test');

        $this->repositoryMock->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($modulo));

        $this->repositoryMock->expects($this->once())
            ->method('save')
            ->will($this->throwException(new UniqueConstrainException()));

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/modulos/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'nombre' => $modulo->getNombre()
            ))
        );

        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(409, $client->getResponse()->getStatusCode());
        $this->assertEquals(409, $jsonResponse['statusCode']);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }
}