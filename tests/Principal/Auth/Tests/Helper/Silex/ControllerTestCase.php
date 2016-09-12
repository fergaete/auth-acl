<?php
namespace Principal\Auth\Tests\Helper\Silex;

use Principal\Auth\Entity\Sistema;
use Principal\Auth\Repository\SistemaRepositoryInterface;

/**
 * Class ControllerTestCase
 * @package Principal\Auth\Tests\Helper\Silex
 */
class ControllerTestCase extends WebTestCase {

    public function setUp() {
        parent::setUp();

        $sistemaRepository = $this->getMockBuilder(SistemaRepositoryInterface::class)->getMock();
        $sistemaRepository->method('findByApiKey')
            ->will($this->returnValue(new Sistema('sistema test', 'apikey test')));

        $this->app['auth.repository.doctrine.orm.sistema'] = $sistemaRepository;
    }
}