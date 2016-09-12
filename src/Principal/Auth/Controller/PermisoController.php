<?php
namespace Principal\Auth\Controller;

use Principal\Auth\Entity\Permiso;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PermisoController
 * @package Principal\Auth\Controller
 */
class PermisoController extends AbstractController {

    /**
     * @var RepositoryInterface
     */
    private $permisoRepository;

    /**
     * @param RepositoryInterface $permisoRepository
     */
    public function __construct(RepositoryInterface $permisoRepository) {
        $this->permisoRepository = $permisoRepository;
    }

    /**
     * @param Application $app
     * @return Response
     */
    public function findAllAction(Application $app) {
        $permisos = $this->permisoRepository->findAll(array());
        return new Response($app['serializer']->serialize($permisos, 'json'), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @param $id
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function findByIdAction($id, Application $app) {
        try {
            $permiso = $this->permisoRepository->findById($id);
            return new Response($app['serializer']->serialize($permiso, 'json'), 200, array('Content-Type' => 'application/json'));
        }
        catch(NotFoundException $ex) {
            return $this->createErrorFromException($ex, 404);
        }
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function newAction(Request $request, Application $app) {
        try {
            $data = $this->extractDataFromRequest($request);
            $permiso = new Permiso(
                $app['auth.repository.doctrine.orm.rol']->findById($data['rol']['id']),
                $app['auth.repository.doctrine.orm.recurso']->findById($data['recurso']['id'])
            );
            $this->permisoRepository->save($permiso);
            return new Response(
                $app['serializer']->serialize($permiso, 'json'),
                201,
                array(
                    'Content-Type' => 'application/json',
                    'Location' => '/api/permisos/' . $permiso->getId()
                ));
        }
        catch(NotFoundException $ex) {
            return $this->createErrorFromException($ex, 404);
        }
        catch(UniqueConstrainException $ex) {
            return $this->createErrorFromException($ex, 409);
        }
        catch(\InvalidArgumentException $ex) {
            return $this->createErrorFromException($ex, 400);
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws \InvalidArgumentException
     */
    public function extractDataFromRequest(Request $request) {
        $message = '%s no enviado o no númerico';
        $data = json_decode($request->getContent(), true);

        if(!is_array($data)) {
            throw new \InvalidArgumentException('data enviada no es de tipo array');
        }

        if(!isset($data['rol']['id']) || !is_numeric($data['rol']['id'])) {
            throw new \InvalidArgumentException(sprintf($message, 'modulo.id'));
        }

        if(!isset($data['recurso']['id']) || !is_numeric($data['recurso']['id'])) {
            throw new \InvalidArgumentException(sprintf($message, 'recurso.id'));
        }

        return $data;
    }
}