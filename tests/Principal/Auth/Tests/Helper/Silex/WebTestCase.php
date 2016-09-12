<?php
namespace Principal\Auth\Tests\Helper\Silex;

use Monolog\Logger;
use Symfony\Component\HttpKernel\HttpKernel;
use Principal\Auth\Repository\Doctrine\ORM\Event\TimestampableCreatedUpdatedEvent;

/**
 * Class WebTestCase
 * @package Principal\Auth\Tests\Helper\Silex
 */
class WebTestCase extends \Silex\WebTestCase {

    /**
     * Creates the application.
     *
     * @return HttpKernel
     */
    public function createApplication() {
        $app = require __DIR__ . '/../../../../../../src/app.php';
        $app['debug'] = true;
        $app['exception_handler']->disable();

        $app['db.options'] = array(
            'driver'   => 'pdo_sqlite',
            'path'     => __DIR__ .'/../../../../../data/app.db'

        );

        $app['serializer.normalizers'][1]->setIgnoredAttributes(array('createdAt', 'updatedAt'));
        $app['orm.em']->getEventManager()->addEventSubscriber(new TimestampableCreatedUpdatedEvent());

        $app['monolog'] =  $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        return $app;
    }
}