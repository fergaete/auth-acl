<?php
namespace Principal\Auth\Silex\Security\ApiKey;

use Principal\Auth\Repository\Exception\NotFoundException;
use Principal\Auth\Repository\SistemaRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ApiKeyUserProvider
 * @package Principal\Auth\Silex\Security\ApiKey
 */
class ApiKeyUserProvider implements ApiKeyUserProviderInterface {

    /**
     * @var SistemaRepositoryInterface
     */
    private $sistemaRepository;

    /**
     * @param SistemaRepositoryInterface $sistemaRepository
     */
    public function __construct(SistemaRepositoryInterface $sistemaRepository) {
        $this->sistemaRepository = $sistemaRepository;
    }

    /**
     * @param $apiKey
     * @return User
     * @throws UsernameNotFoundException
     */
    public function loadUserByApiKey($apiKey) {
        try {
            $sistema = $this->sistemaRepository->findByApiKey($apiKey);
            return new User(
                $sistema->getNombre(),
                null,
                array('API')
            );
        }
        catch(NotFoundException $ex) {
            throw new UsernameNotFoundException(sprintf('Apikey %s no encontrada', $apiKey));
        }
    }

    /**
     * @param $username
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($username) {
       throw new UsernameNotFoundException();
    }

    /**
     * @param UserInterface $user
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user) {
        throw new UnsupportedUserException();
    }

    /**
     * @param $class
     * @return bool
     */
    public function supportsClass($class) {
        return 'Symfony\Component\Security\Core\User\User' === $class;
    }

}