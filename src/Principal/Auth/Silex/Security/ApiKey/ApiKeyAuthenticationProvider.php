<?php
namespace Principal\Auth\Silex\Security\ApiKey;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Principal\Auth\Silex\Security\Authentication\Token\ApiKeyToken;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ApiKeyAuthenticationProvider
 * @package Principal\Auth\Silex\Security\ApiKey
 */
class ApiKeyAuthenticationProvider implements AuthenticationProviderInterface {

    /**
     * @var ApiKeyUserProviderInterface
     */
    private $userProvider;

    /**
     * @var PasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @param ApiKeyUserProviderInterface $userProvider
     * @param PasswordEncoderInterface $passwordEncoder
     */
    public function __construct(ApiKeyUserProviderInterface $userProvider, PasswordEncoderInterface $passwordEncoder) {
        $this->userProvider = $userProvider;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param TokenInterface $token
     * @return ApiKeyToken|TokenInterface
     * @throws AuthenticationException
     */
    public function authenticate(TokenInterface $token) {
        $user = $this->userProvider->loadUserByApiKey($this->passwordEncoder->encodePassword($token->getCredentials(), ''));
        if (!$user || !($user instanceof UserInterface)) {
            throw new AuthenticationException('Bad credentials');
        }
        $token = new ApiKeyToken($token->getCredentials(), $user->getRoles());
        $token->setUser($user);
        return $token;
    }

    /**
     * @param TokenInterface $token
     * @return bool
     */
    public function supports(TokenInterface $token) {
        return $token instanceof ApiKeyToken;
    }
}