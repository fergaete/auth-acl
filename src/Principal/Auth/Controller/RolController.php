<?php
namespace Principal\Auth\Controller;

use Principal\Auth\Entity\Rol;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RolController
 * @package Planok\Auth\Controller
 */
class RolController extends AbstractController {

    /**
     * @var RepositoryInterface
     */
    private $rolRepository;

    /**
     * @param RepositoryInterface $rolRepository
     */
    public function __construct(RepositoryInterface $rolRepository) {
        $this->rolRepository = $rolRepository;
    }

    /**
     * @param Application $app
     * @return Response
     */
    public function findAllAction(Application $app) {
        $roles = $this->rolRepository->findAll(array());
        return new Response($app['serializer']->serialize($roles, 'json'), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @param $id
     * @param Application $app
     * @return JsonResponse|Response
     */
    public function findByIdAction($id, Application $app) {
        try {
            $rol = $this->rolRepository->findById($id);
            return new Response($app['serializer']->serialize($rol, 'json'), 200, array('Content-Type' => 'application/json'));
        }
        catch(NotFoundException $ex) {
            return new JsonResponse(array(
                'statusCode' => 404,
                'message'    => $ex->getCode(),
                'stackTrace' => $ex->getTraceAsString()
            ), 404);
        }
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return JsonResponse|Response
     */
    public function newAction(Request $request, Application $app) {
        try {
            $data = json_decode($request->getContent(), true);
            if (!$this->isDataValid($data)) {
                return new JsonResponse(array(
                    'statusCode' => 400,
                    'message' => 'campo nombre no enviado',
                    'stackTrace' => ''
                ), 400);
            }

            $rol = new Rol($data['nombre']);
            $this->rolRepository->save($rol);
            return new Response(
                $app['serializer']->serialize($rol, 'json'),
                201,
                array(
                    'Content-Type' => 'application/json',
                    'Location' => '/api/roles/' . $rol->getId()
                ));
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
        $data = json_decode($request->getContent(), true);

        if(!$this->isDataValid($data)) {
            return new JsonResponse(array(
                'statusCode' => 400,
                'message'    => 'campo nombre no enviado',
                'stackTrace' => ''
            ), 400);
        }

        try {
            $rol = $this->rolRepository->findById($id);
            $rol->setNombre($data['nombre']);
            $this->rolRepository->save($rol);
            return new Response($app['serializer']->serialize($rol, 'json'), 200, array('Content-Type' => 'application/json'));
        }
        catch(NotFoundException $ex) {
            return $this->createErrorFromException($ex, 404);
        }
        catch(UniqueConstrainException $ex) {
            return $this->createErrorFromException($ex, 409);
        }
    }
}