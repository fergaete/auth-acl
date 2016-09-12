<?php
namespace Principal\Auth\Controller;

use Principal\Auth\Repository\Doctrine\ORM\SistemaRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractController
 * @package Principal\Auth\Controller
 */
class AbstractController {

    /**
     * @param \Exception $ex
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function createErrorFromException(\Exception $ex, $statusCode = 500) {
        return new JsonResponse(array(
            'statusCode' => (int) $statusCode,
            'message'    => $ex->getMessage(),
            'stackTrace' => $ex->getTraceAsString()
        ), $statusCode);
    }

    /**
     * @param $data
     * @return bool
     */
    protected function isDataValid($data) {
        return is_array($data) && array_key_exists('nombre', $data) && is_string($data['nombre']);
    }

    /**
     * @param Request $request
     * @param array|null $map
     * @return array|null
     */
    protected function createSort(Request $request, array $map = null) {
        $sort = null;

        if($request->get('sort')) {
            foreach(explode(',', $request->get('sort')) as $field) {
                if(preg_match('/^[+|-]{1}/', $field, $matches)) {
                    list($order, $fieldName) = explode($matches[0], $field);
                    $dir = ($matches[0] == '+') ? 'ASC' : 'DESC';
                }
                else {
                    $fieldName = $field;
                    $dir = 'ASC';
                }

                if(is_array($map) && array_key_exists($fieldName, $map)) {
                    $fieldName = $map[$fieldName];
                }

                $sort[$fieldName] = $dir;
            }
        }

        return $sort;
    }

    /**
     * @param Request $request
     * @param array $map
     * @return array
     */
    protected function createCriteria(Request $request, array $map) {
        $criteria = array();

        foreach($map as $expected => $transform) {
            if($request->get($expected)) {
                $criteria[$transform] = $request->get($expected);
            }
        }

        return $criteria;
    }
}