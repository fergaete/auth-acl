<?php
namespace Principal\Auth\Controller;

use Principal\Auth\Entity\Accion;
use Principal\Auth\Entity\Modulo;
use Principal\Auth\Entity\Recurso;
use Principal\Auth\Entity\RolLicitacion;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RolLicitacionRepositoryInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RolLicitacionController
 * @package Principal\Auth\Controller
 */
class RolLicitacionController extends AbstractController {

    const NEW_ACTION_DATA   = 1;
    const TIENE_ACCESO_DATA = 2;

    /**
     * @var RolLicitacionRepositoryInterface
     */
    private $rolLicitacionRepository;

    /**
     * @param RolLicitacionRepositoryInterface $rolLicitacionRepository
     */
    public function __construct(RolLicitacionRepositoryInterface $rolLicitacionRepository) {
        $this->rolLicitacionRepository = $rolLicitacionRepository;
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return Response
     */
    public function findAllAction(Request $request, Application $app) {
        $map = array('id' => 'this.id', 'idLicitacion' => 'this.idLicitacion', 'idUsuario' => 'this.idUsuario', 'rol_id' => 'rol.id');
        $rolLicitacionCollection = $this->rolLicitacionRepository->findAll(
            $this->createCriteria($request, $map),
            $this->createSort($request, $map)
        );

        return new Response($app['serializer']->serialize($rolLicitacionCollection, 'json'), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @param $id
     * @param Application $app
     * @return JsonResponse|Response
     */
    public function findByIdAction($id, Application $app) {
        try {
            $rolLicitacion = $this->rolLicitacionRepository->findById($id);
            return new Response($app['serializer']->serialize($rolLicitacion, 'json'), 200, array('Content-Type' => 'application/json'));
        }
        catch(NotFoundException $ex) {
            return $this->createErrorFromException($ex, 404);
        }
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return JsonResponse|Response
     */
    public function newAction(Request $request, Application $app) {
        try {
            $data = $this->extractDataFromRequest($request);
            $rol = $app['auth.repository.doctrine.orm.rol']->findById($data['rol']['id']);
            $rolLicitacion = new RolLicitacion($rol, $data['idLicitacion'], $data['idUsuario']);
            $this->rolLicitacionRepository->save($rolLicitacion);

            return new Response(
                $app['serializer']->serialize($rolLicitacion, 'json'),
                201,
                array(
                    'Location' => '/api/rol-licitaciones/' . $rolLicitacion->getId(),
                    'Content-Type' => 'application/json'
                ));
        }
        catch(\InvalidArgumentException $ex) {
            return $this->createErrorFromException($ex, 400);
        }
        catch(NotFoundException $ex) {
            return $this->createErrorFromException($ex, 404);
        }
        catch(UniqueConstrainException $ex) {
            return $this->createErrorFromException($ex, 409);
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @param Application $app
     * @return JsonResponse|Response
     */
    public function updateAction($id, Request $request, Application $app) {
        try {
            $data = $this->extractDataFromRequest($request);
            $rolLicitacion = $this->rolLicitacionRepository->findById($id);
            $rolLicitacion->setIdLicitacion($data['idLicitacion']);
            $rolLicitacion->setIdUsuario($data['idUsuario']);
            $rolLicitacion->setRol($app['auth.repository.doctrine.orm.rol']->findById($data['rol']['id']));

            $this->rolLicitacionRepository->save($rolLicitacion);
            return new Response(
                $app['serializer']->serialize($rolLicitacion, 'json'),
                200,
                array(
                    'Content-Type' => 'application/json'
                )
            );
        }
        catch(\InvalidArgumentException $ex) {
            return $this->createErrorFromException($ex, 400);
        }
        catch(NotFoundException $ex) {
            return $this->createErrorFromException($ex, 404);
        }
        catch(UniqueConstrainException $ex) {
            return $this->createErrorFromException($ex, 409);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function tieneAccesoAction(Request $request) {
        try {
            $data = $this->extractDataFromRequest($request, self::TIENE_ACCESO_DATA);
            $rolLicitacionCollection = $this->rolLicitacionRepository->findByIdLicitacionAndIdUsuarioAndRecurso(
                $data['idLicitacion'],
                $data['idUsuario'],
                new Recurso(new Modulo($data['recurso']['modulo']['nombre']), new Accion($data['recurso']['accion']['nombre']))
            );

            if ($rolLicitacionCollection->count() == 0) {
                return new JsonResponse(array(
                    'statusCode' => 403,
                    'message' => 'acceso denegado',
                    'stackTrace' => ''
                ), 403);
            }

            return new JsonResponse('');
        }
        catch(\InvalidArgumentException $ex) {
            return $this->createErrorFromException($ex, 400);
        }
    }

    /**
     * @param Request $request
     * @param int $type
     * @return array
     * @throws \InvalidArgumentException
     */
    private function extractDataFromRequest(Request $request, $type = self::NEW_ACTION_DATA) {
        $data = json_decode($request->getContent(), true);
        $message = 'el campo %s no existe o el tipo de dato es incorrecto';

        if(!is_array($data)) {
            throw new \InvalidArgumentException('data debe ser un arreglo');
        }

        if(!isset($data['idLicitacion']) || !is_numeric($data['idLicitacion'])) {
            throw new \InvalidArgumentException(sprintf($message, 'idLicitacion'));
        }

        if(!isset($data['idUsuario']) || !is_numeric($data['idUsuario'])) {
            throw new \InvalidArgumentException(sprintf($message, 'idUsuario'));
        }

        if($type == self::NEW_ACTION_DATA && (!isset($data['rol']['id']) || !is_numeric($data['rol']['id']))) {
            throw new \InvalidArgumentException(sprintf($message, 'rol.id'));
        }

        if($type == self::TIENE_ACCESO_DATA && (!isset($data['recurso']['modulo']['nombre']) || !is_string($data['recurso']['modulo']['nombre']))) {
            throw new \InvalidArgumentException(sprintf($message, 'recurso.modulo.nombre'));
        }

        if($type == self::TIENE_ACCESO_DATA && (!isset($data['recurso']['accion']['nombre']) || !is_string($data['recurso']['accion']['nombre']))) {
            throw new \InvalidArgumentException(sprintf($message, 'recurso.accion.nombre'));
        }

        return $data;
    }
}