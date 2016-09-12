<?php
namespace Principal\Auth\Controller;

use Principal\Auth\Entity\Accion;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AccionController
 * @package Principal\Auth\Controller
 */
class AccionController extends AbstractController {

    /**
     * @var RepositoryInterface
     */
    private $accionRepository;

    /**
     * @param RepositoryInterface $accionRepository
     */
    public function __construct(RepositoryInterface $accionRepository) {
        $this->accionRepository = $accionRepository;
    }

    /**
     * @param Application $app
     * @return Response
     */
    public function findAll(Application $app) {
        $acciones = $app['serializer']->serialize($this->accionRepository->findAll(array())->toArray(), 'json');
        return new Response($acciones, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @param $id
     * @param Application $app
     * @return JsonResponse|Response
     */
    public function findById($id, Application $app) {
        try {
            $accion = $this->accionRepository->findById($id);
            return new Response($app['serializer']->serialize($accion, 'json'), 200, array('Content-Type' => 'application/json'));
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
        $data = json_decode($request->getContent(), true);

        if(!$this->isDataValid($data)) {
            return new JsonResponse(array(
                'statusCode' => 400,
                'message'    => 'campo nombre no enviado',
                'stackTrace' => ''
            ),400);
        }

        try {
            $accion = new Accion($data['nombre']);
            $this->accionRepository->save($accion);
            $response = new Response($app['serializer']->serialize($accion, 'json'), 201, array('Content-Type' => 'application/json'));
            $response->headers->set(
                "Location",
                sprintf("/api/accion/%d", $accion->getId())
            );
            return $response;
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
            ),400);
        }

        try {
            $accion = $this->accionRepository->findById($id);
            $accion->setNombre($data['nombre']);
            $this->accionRepository->save($accion);
            return new Response($app['serializer']->serialize($accion, 'json'), 200, array('Content-Type' => 'application/json'));
        }
        catch(NotFoundException $ex) {
            return $this->createErrorFromException($ex, 404);
        }
        catch(UniqueConstrainException $ex) {
            return $this->createErrorFromException($ex, 409);
        }
    }
}