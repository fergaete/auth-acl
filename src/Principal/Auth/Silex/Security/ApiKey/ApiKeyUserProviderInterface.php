<?php
namespace Principal\Auth\Silex\Security\ApiKey;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Interface ApiKeyUserProviderInterface
 * @package Principal\Auth\Silex\Security\ApiKey
 */
interface ApiKeyUserProviderInterface extends UserProviderInterface {

    /**
     * @param string $apiKey
     * @return UserInterface
     * @throws UsernameNotFoundException
     */
    public function loadUserByApiKey($apiKey);
}