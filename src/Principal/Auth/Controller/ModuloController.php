<?php
namespace Principal\Auth\Controller;

use Principal\Auth\Entity\Modulo;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\Doctrine\ORM\ModuloRepository;
use Principal\Auth\Repository\RepositoryInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ModuloController
 * @package Principal\Auth\Controller
 */
class ModuloController extends AbstractController {

    /**
     * @var RepositoryInterface
     */
    private $moduloRepository;

    /**
     * @param RepositoryInterface $moduloRepository
     */
    public function __construct(RepositoryInterface $moduloRepository) {
        $this->moduloRepository = $moduloRepository;
    }

    /**
     * @param Application $app
     * @return Response
     */
    public function findAll(Application $app) {
        $modulos = $this->moduloRepository->findAll(array());
        return new Response($app['serializer']->serialize($modulos, 'json'), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @param $id
     * @param Application $app
     * @return JsonResponse|Response
     */
    public function findById($id, Application $app) {
        try {
            $modulo = $this->moduloRepository->findById($id);
            return new Response($app['serializer']->serialize($modulo, 'json'), 200, array('Content-Type' => 'application/json'));
        }
        catch(NotFoundException $ex) {
           return $this->createErrorFromException($ex, 404);
        }
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function newAction(Application $app, Request $request) {
        try {
            $data = json_decode($request->getContent(), true);
            if(!$this->isDataValid($data)) {
                return new JsonResponse(array(
                    'statusCode' => 400,
                    'message'    => 'campo nombre no fue enviado',
                    'stackTrace' => ''
                ), 400);
            }

            $modulo = new Modulo($data['nombre']);
            $this->moduloRepository->save($modulo);
            return new Response(
                $app['serializer']->serialize($modulo, 'json'),
                201,
                array(
                    'Location'     => '/api/modulos/' . $modulo->getId(),
                    'Content-Type' => 'application/json'
                ));
        }
        catch(UniqueConstrainException $ex) {
            return $this->createErrorFromException($ex, 409);
        }
    }

    /**
     * @param $id
     * @param Application $app
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function updateAction($id, Application $app, Request $request) {
        try {
            $data = json_decode($request->getContent(), true);
            $modulo = $this->moduloRepository->findById($id);
            $modulo->setNombre($data['nombre']);
            $this->moduloRepository->save($modulo);
            return new Response($app['serializer']->serialize($modulo, 'json'),
                200,
                array(
                    'Location'     => '/api/modulos/' . $modulo->getId(),
                    'Content-Type' => 'application/json'
                ));
        }
        catch(NotFoundException $ex) {
           return $this->createErrorFromException($ex, 404);
        }
        catch(UniqueConstrainException $ex) {
            return $this->createErrorFromException($ex, 409);
        }
    }
}