<?php
$app = require_once __DIR__ . '/../src/bootstrap.php';
$app->error(function (\Exception $ex, $statusCode) {
    return new \Symfony\Component\HttpFoundation\JsonResponse(array(
        'statusCode' => $statusCode,
        'message'    => $ex->getMessage(),
        'stackTrace' => $ex->getTraceAsString()
    ), $statusCode);
});
$app->run();