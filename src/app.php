<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Silex\Application;

use Igorw\Silex\ConfigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SerializerServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Principal\Auth\Silex\Provider\ApiKeyServiceProvider;
use Principal\Auth\Silex\Security\ApiKey\ApiKeyUserProvider;
use Principal\Auth\Silex\Security\ApiKey\ApiKeyPasswordEncoder;
use Silex\Provider\SecurityServiceProvider;
use JDesrosiers\Silex\Provider\SwaggerServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Principal\Auth\Repository\Doctrine\ORM\SistemaRepository;
use Principal\Auth\Repository\Doctrine\ORM\AccionRepository;
use Principal\Auth\Repository\Doctrine\ORM\ModuloRepository;
use Principal\Auth\Repository\Doctrine\ORM\RecursoRepository;
use Principal\Auth\Repository\Doctrine\ORM\RolRepository;
use Principal\Auth\Repository\Doctrine\ORM\PermisoRepository;
use Principal\Auth\Repository\Doctrine\ORM\RolLicitacionRepository;
use Principal\Auth\Controller\AccionController;
use Principal\Auth\Controller\ModuloController;
use Principal\Auth\Controller\RecursoController;
use Principal\Auth\Controller\RolController;
use Principal\Auth\Controller\PermisoController;
use Principal\Auth\Controller\RolLicitacionController;


$app = new Application();

$app->register(new ConfigServiceProvider( __DIR__ . "/../config/app/config.php"));

$app['debug'] = $app['auth.debug'];
ini_set('date.timezone',$app['auth.date.timezone']);

$app['auth.repository.doctrine.orm.sistema'] = $app->share(function() use ($app) {
    return new SistemaRepository($app['orm.em']);
});
$app['auth.repository.doctrine.orm.accion'] = $app->share(function() use ($app) {
    return new AccionRepository($app['orm.em']);
});
$app['auth.repository.doctrine.orm.modulo'] = $app->share(function() use ($app) {
    return new ModuloRepository($app['orm.em']);
});
$app['auth.repository.doctrine.orm.recurso'] = $app->share(function() use ($app) {
    return new RecursoRepository($app['orm.em']);
});
$app['auth.repository.doctrine.orm.rol'] = $app->share(function() use ($app) {
    return new RolRepository($app['orm.em']);
});
$app['auth.repository.doctrine.orm.permiso'] = $app->share(function() use ($app) {
    return new PermisoRepository($app['orm.em']);
});
$app['auth.repository.doctrine.orm.rol_licitacion'] = $app->share(function() use ($app) {
    return new RolLicitacionRepository($app['orm.em']);
});

$app->register(new ServiceControllerServiceProvider());
$app->register(new SerializerServiceProvider());
$app->register(new DoctrineServiceProvider(), array('db.options' => $app["auth.database"]));
$app->register(new DoctrineOrmServiceProvider, array(
    'orm.em.options' => array(
        'mappings' => array(
            array(
                'type'      => 'yml',
                'namespace' => 'Principal\Auth\Entity',
                'path'      => __DIR__ . '/../config/doctrine',
            )
        )
    )
));
$app->register(
    new SwaggerServiceProvider(),
    array(
        'swagger.srcDir'      => __DIR__ . '/../vendor/zircote/swagger-php/library',
        'swagger.servicePath' => __DIR__ . '/Principal/Auth',
        'swagger.apiVersion'  => $app['auth.api.version'],
    )
);

$app->register(new ApiKeyServiceProvider(), array(
    'api_key.user_provider'    => $app->share(function() use($app) {
        return new ApiKeyUserProvider($app['auth.repository.doctrine.orm.sistema']);
    }),
    'api_key.password_encoder' => new ApiKeyPasswordEncoder()
));

$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'secured' => array(
            'pattern'   => '/api',
            'api_key'   => true,
            'stateless' => true
        )
    )));

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../logs/application/' . date('Y-m-d') . ".log",
    'monolog.name'    => 'auth-api'
));

$app['auth.controller.accion'] = $app->share(function() use ($app) {
    return new AccionController($app['auth.repository.doctrine.orm.accion']);
});
$app['auth.controller.modulo'] = $app->share(function() use ($app) {
    return new ModuloController($app['auth.repository.doctrine.orm.modulo']);
});
$app['auth.controller.recurso'] = $app->share(function() use ($app) {
    return new RecursoController($app['auth.repository.doctrine.orm.recurso']);
});
$app['auth.controller.rol'] = $app->share(function() use ($app) {
    return new RolController($app['auth.repository.doctrine.orm.rol']);
});
$app['auth.controller.permiso'] = $app->share(function() use ($app) {
    return new PermisoController($app['auth.repository.doctrine.orm.permiso']);
});
$app['auth.controller.rol_licitacion'] = $app->share(function() use ($app) {
    return new RolLicitacionController($app['auth.repository.doctrine.orm.rol_licitacion']);
});

$app->get('/api/acciones', 'auth.controller.accion:findAll');
$app->get('/api/acciones/{id}', 'auth.controller.accion:findById')
    ->assert('id', '\d+');
$app->post('/api/acciones', 'auth.controller.accion:newAction');
$app->put('/api/acciones/{id}', 'auth.controller.accion:updateAction')
    ->assert('id', '\d+');

$app->get('/api/modulos', 'auth.controller.modulo:findAll');
$app->get('/api/modulos/{id}', 'auth.controller.modulo:findById')
    ->assert('id', '\d+');
$app->post('/api/modulos', 'auth.controller.modulo:newAction');
$app->put('/api/modulos/{id}', 'auth.controller.modulo:updateAction')
    ->assert('id', '\d+');

$app->get('/api/recursos', 'auth.controller.recurso:findAllAction');
$app->get('/api/recursos/{id}', 'auth.controller.recurso:findByIdAction')
    ->assert('id', '\d+');
$app->post('/api/recursos', 'auth.controller.recurso:newAction');

$app->get('/api/roles', 'auth.controller.rol:findAllAction');
$app->get('/api/roles/{id}', 'auth.controller.rol:findByIdAction')
    ->assert('id', '\d+');
$app->post('/api/roles', 'auth.controller.rol:newAction');
$app->put('/api/roles/{id}', 'auth.controller.rol:updateAction')
    ->assert('id', '\d+');

$app->get('/api/permisos', 'auth.controller.permiso:findAllAction');
$app->get('/api/permisos/{id}', 'auth.controller.permiso:findByIdAction')
    ->assert('id', '\d+');
$app->post('/api/permisos', 'auth.controller.permiso:newAction');

$app->get('/api/rol-licitaciones', 'auth.controller.rol_licitacion:findAllAction');
$app->get('/api/rol-licitaciones/{id}', 'auth.controller.rol_licitacion:findByIdAction')
    ->assert('id','\d+');
$app->post('/api/rol-licitaciones', 'auth.controller.rol_licitacion:newAction');
$app->post('/api/rol-licitaciones/tiene-acceso', 'auth.controller.rol_licitacion:tieneAccesoAction');
$app->put('/api/rol-licitaciones/{id}', 'auth.controller.rol_licitacion:updateAction')
    ->assert('id', '\d+');

return $app;