<?php
namespace Principal\Auth\Tests\Controller;

use Principal\Auth\Entity\Collection\RolLicitacionCollection;
use Principal\Auth\Entity\Rol;
use Principal\Auth\Entity\RolLicitacion;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;
use Principal\Auth\Repository\RolLicitacionRepositoryInterface;
use Principal\Auth\Tests\Helper\Silex\ControllerTestCase;
use Principal\Auth\Tests\Helper\Silex\WebTestCase;

/**
 * Class RolLicitacionControllerTest
 * @package Principal\Auth\Tests\Controller
 */
class RolLicitacionControllerTest extends ControllerTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rolLicitacionRepository;

    public function setUp() {
        parent::setUp();
        $this->rolLicitacionRepository = $this->getMockBuilder(RolLicitacionRepositoryInterface::class)->getMock();
        $this->app['auth.repository.doctrine.orm.rol_licitacion'] = $this->rolLicitacionRepository;
    }

    private function rolLicitacionToArray(RolLicitacion $rolLicitacion) {
        return array(
            'id'           => $rolLicitacion->getId(),
            'idLicitacion' => $rolLicitacion->getIdLicitacion(),
            'idUsuario'    => $rolLicitacion->getIdUsuario(),
            'rol' => array(
                'id'     => $rolLicitacion->getRol()->getId(),
                'nombre' => $rolLicitacion->getRol()->getNombre()
            )
        );
    }

    /**
     * @test
     */
    public function findAllAction_debeRetornar200YTodosLosRecursosEnJSON() {
        $rolLicitacion = new RolLicitacion(new Rol('rol test'), 1, 1);
        $rolLicitacionCollection = new RolLicitacionCollection();
        $rolLicitacionCollection->add($rolLicitacion);

        $this->rolLicitacionRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($rolLicitacionCollection));

        $client = $this->createClient();
        $client->request('GET', '/api/rol-licitaciones?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(array($this->rolLicitacionToArray($rolLicitacion)), json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function findAllAction_conParametroIdLicitacion_debeRetornar200YUnaCollectionDeRolLicitacion() {
        $rolLicitacion = new RolLicitacion(new Rol('rol1'), 1, 1);
        $this->rolLicitacionRepository->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo(array('this.idLicitacion' => '1')))
            ->will($this->returnValue(new RolLicitacionCollection(array($rolLicitacion))));

        $client = $this->createClient();
        $client->request('GET', '/api/rol-licitaciones?apikey=test&idLicitacion=1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(array($this->rolLicitacionToArray($rolLicitacion)), json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function findAllAction_ConParametroSort_debeRetornar200YTodosLosRolesLicitacionOrdenadosEnJSON(){
        $rolLicitacion = new RolLicitacion(new Rol('rol1'), 1, 1);
        $this->rolLicitacionRepository->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo(array()), $this->equalTo(array('rol.id' => 'ASC', 'this.idLicitacion' => 'DESC')))
            ->will($this->returnValue(new RolLicitacionCollection(array($rolLicitacion))));

        $client = $this->createClient();
        $client->request('GET', '/api/rol-licitaciones?'. http_build_query(array('apikey' => 'test', 'sort' => '+rol.id,-idLicitacion')));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(array($this->rolLicitacionToArray($rolLicitacion)), json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoUnRecursoExistente_findByIdAction_debeRetornarUn200YJSON() {
        $rolLicitacion = new RolLicitacion(new Rol('rol test'), 1, 1);

        $this->rolLicitacionRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($rolLicitacion));

        $client = $this->createClient();
        $client->request('GET', '/api/rol-licitaciones/1?apikey=test');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($this->rolLicitacionToArray($rolLicitacion), json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoUnaRecursoNoExistente_findByIdAction_debeRetornarNotFound404() {
        $this->rolLicitacionRepository->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));

        $client = $this->createClient();
        $client->request('GET', '/api/rol-licitaciones/1?apikey=test');

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 404);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);

    }

    /**
     * @test
     */
    public function dadoDataValidaYUnRolExistente_newAction_debeRetornarUn201ConLocationYObjetoSerializadoEnJSON() {
        $rol = new Rol('rol test');
        $rolRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($rol));
        $this->app['auth.repository.doctrine.orm.rol'] = $rolRepository;

        $this->rolLicitacionRepository->expects($this->once())
            ->method('save');

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/rol-licitaciones?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'rol' => array(
                    'id' => 1
                ),
                'idLicitacion' => 1,
                'idUsuario'    => 1
            ))
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertContains('/api/rol-licitaciones', $client->getResponse()->headers->get('Location'));
        $this->assertEquals($this->rolLicitacionToArray(new RolLicitacion($rol, 1, 1)), json_decode($client->getResponse()->getContent(), true));

    }

    /**
     * @test
     */
    public function dadoDataInvalida_newAction_debeRetornarUnBadRequest400YJSONDeError() {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/rol-licitaciones?apikey=test',
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

    /**
     * @test
     */
    public function dadoUnRolNoExistente_newAction_debeRetornarUn404YJSONDeError() {
        $rolRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));
        $this->app['auth.repository.doctrine.orm.rol'] = $rolRepository;

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/rol-licitaciones?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'rol' => array(
                    'id' => 1
                ),
                'idLicitacion' => 1,
                'idUsuario'    => 1
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
    public function dadoUnRolLicitacionDuplicado_newAction_debeRetornarUn409ConflictYJSONDeError() {
        $rol = new Rol('rol test');
        $rolRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($rol));
        $this->app['auth.repository.doctrine.orm.rol'] = $rolRepository;

        $this->rolLicitacionRepository->expects($this->once())
            ->method('save')
            ->will($this->throwException(new UniqueConstrainException()));

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/rol-licitaciones?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'rol' => array(
                    'id' => 1
                ),
                'idLicitacion' => 1,
                'idUsuario'    => 1
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
    public function dadoDataInvalida_updateAction_debeRetornarUnBadRequest400YJSONDeError() {
        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/rol-licitaciones/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            ''
        );

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 400);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);
    }

    /**
     * @test
     */
    public function dadoUnRolLicitacionNoExistente_updateAction_DebeRetornar404YJsonConError() {
        $this->rolLicitacionRepository->expects($this->once())
            ->method('findById')
            ->will($this->throwException(new NotFoundException()));

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/rol-licitaciones/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'rol' => array(
                    'id' => 1
                ),
                'idLicitacion' => 1,
                'idUsuario'    => 1
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
    public function dadoUnRolLicitacionDuplicado_updateAction_DebeRetornarUn409YJsonConError() {
        $rolRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(new Rol('test rol')));

        $this->app['auth.repository.doctrine.orm.rol'] = $rolRepository;

        $this->rolLicitacionRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(new RolLicitacion(new Rol('rol'), 1, 1)));

        $this->rolLicitacionRepository->expects($this->once())
            ->method('save')
            ->will($this->throwException(new UniqueConstrainException()));

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/rol-licitaciones/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'rol' => array(
                    'id' => 1
                ),
                'idLicitacion' => 1,
                'idUsuario'    => 1
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
    public function dadoUnRolLicitacionExistente_updateAction_DebeActualizarRegistroYRetornar200() {
        $rolRepository = $this->getMockBuilder(RepositoryInterface::class)->getMock();
        $rolRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue(new Rol('test rol')));

        $this->app['auth.repository.doctrine.orm.rol'] = $rolRepository;

        $rolLicitacion = new RolLicitacion(new Rol('rol'), 1, 1);
        $this->rolLicitacionRepository->expects($this->once())
            ->method('findById')
            ->will($this->returnValue($rolLicitacion));

        $this->rolLicitacionRepository->expects($this->once())
            ->method('save');

        $client = $this->createClient();
        $client->request(
            'PUT',
            '/api/rol-licitaciones/1?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'rol' => array(
                    'id' => 1
                ),
                'idLicitacion' => 1,
                'idUsuario'    => 1
            ))
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($this->rolLicitacionToArray($rolLicitacion), json_decode($client->getResponse()->getContent(), true));
    }

    /**
     * @test
     */
    public function dadoUnUsuarioConPermisoParaUnaLicitacionDada_tieneAcceso_DebeRetornar200() {
        $rolLicitacionCollection = new RolLicitacionCollection();
        $rolLicitacionCollection->add(new RolLicitacion(new Rol('rol'), 1, 1));
        $this->rolLicitacionRepository
            ->expects($this->once())
            ->method('findByIdLicitacionAndIdUsuarioAndRecurso')
            ->will($this->returnValue($rolLicitacionCollection));

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/rol-licitaciones/tiene-acceso?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'idLicitacion' => 1,
                'idUsuario'    => 1,
                'recurso' => array(
                    'modulo' => array(
                        'nombre' => 'modulo 1'
                    ),
                    'accion' => array(
                        'nombre' => 'accion 1'
                    )
                )))
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function dadoUnUsuarioSinPermisoParaUnaLicitacionDada_tieneAcceso_DebeRetornar403ForbiddenYJsonConError() {
        $this->rolLicitacionRepository
            ->expects($this->once())
            ->method('findByIdLicitacionAndIdUsuarioAndRecurso')
            ->will($this->returnValue(new RolLicitacionCollection()));

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/rol-licitaciones/tiene-acceso?apikey=test',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'idLicitacion' => 1,
                'idUsuario'    => 1,
                'recurso' => array(
                    'modulo' => array(
                        'nombre' => 'modulo 1'
                    ),
                    'accion' => array(
                        'nombre' => 'accion 1'
                    )
                )))
        );

        $jsonResponse =  json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $this->assertEquals($jsonResponse['statusCode'], 403);
        $this->assertArrayHasKey('message', $jsonResponse);
        $this->assertArrayHasKey('stackTrace', $jsonResponse);

    }

    /**
     * @test
     */
    public function dadoDataInvalida_tieneAcceso_DebeRetornar400BadRequestYJsonConError() {
        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/rol-licitaciones/tiene-acceso?apikey=test',
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