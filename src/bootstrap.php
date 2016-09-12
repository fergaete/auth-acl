<?php
$app = require_once __DIR__ . '/app.php';
$app['serializer.normalizers'][1]->setIgnoredAttributes(array('createdAt', 'updatedAt'));
$app['orm.em']->getEventManager()->addEventSubscriber(new \Principal\Auth\Repository\Doctrine\ORM\Event\TimestampableCreatedUpdatedEvent());
return $app;