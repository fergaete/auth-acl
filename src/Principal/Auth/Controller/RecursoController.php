<?php
namespace Principal\Auth\Controller;

use Principal\Auth\Entity\Recurso;
use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\Exception\UniqueConstrainException;
use Principal\Auth\Repository\RepositoryInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RecursoController
 * @package Planok\Auth\Controller
 */
class RecursoController extends AbstractController {

    /**
     * @var RepositoryInterface
     */
    private $recursoRepository;

    /**
     * @param RepositoryInterface $recursoRepository
     */
    public function __construct(RepositoryInterface $recursoRepository) {
        $this->recursoRepository = $recursoRepository;
    }

    public function findAllAction(Application $app) {
        $recursos = $this->recursoRepository->findAll(array());
        return new Response($app['serializer']->serialize($recursos, 'json'), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @param $id
     * @param Application $app
     * @return Response
     */
    public function findByIdAction($id, Application $app) {
        try {
            $recurso = $this->recursoRepository->findById($id);
            return new Response($app['serializer']->serialize($recurso, 'json'), 200, array('Content-Type' => 'application/json'));
        }
        catch(NotFoundException $ex) {
            return $this->createErrorFromException($ex, 404);
        }
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return Response
     */
    public function newAction(Request $request, Application $app) {
        try {
            $data = $this->extractDataFromRequest($request);
            $recurso = new Recurso(
                $app['auth.repository.doctrine.orm.modulo']->findById($data['modulo']['id']),
                $app['auth.repository.doctrine.orm.accion']->findById($data['accion']['id'])
            );
            $this->recursoRepository->save($recurso);
            return new Response(
                $app['serializer']->serialize($recurso, 'json'),
                201,
                array(
                    'Content-Type' => 'application/json',
                    'Location'     => '/api/recursos/' . $recurso->getId()
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
     * @return mixed
     */
    private function extractDataFromRequest(Request $request) {
        $message = '%s no enviado o no númerico';
        $data = json_decode($request->getContent(), true);

        if(!is_array($data)) {
            throw new \InvalidArgumentException('data enviada no es de tipo array');
        }

        if(!isset($data['modulo']['id']) || !is_numeric($data['modulo']['id'])) {
            throw new \InvalidArgumentException(sprintf($message, 'modulo.id'));
        }

        if(!isset($data['accion']['id']) || !is_numeric($data['accion']['id'])) {
            throw new \InvalidArgumentException(sprintf($message, 'accion.id'));
        }

        return $data;
    }
}