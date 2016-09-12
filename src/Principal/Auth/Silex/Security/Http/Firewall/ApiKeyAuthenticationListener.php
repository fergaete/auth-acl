<?php
namespace Principal\Auth\Silex\Security\Http\Firewall;

use Principal\Auth\Silex\Security\Authentication\Token\ApiKeyToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * Class ApiKeyAuthenticationListener
 * @package Principal\Auth\Silex\Security\Http\Firewall
 */
class ApiKeyAuthenticationListener implements ListenerInterface {

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @param SecurityContextInterface $securityContext
     * @param AuthenticationManagerInterface $authenticationManager
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager) {

        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event) {
        $apiKey = $event->getRequest()->get('apikey');

        if(!$apiKey) {
            $event->setResponse(new Response('autentication failed', 403));
            return;
        }

        try {
            $token = $this->authenticationManager->authenticate(new ApiKeyToken($apiKey));
            $this->securityContext->setToken($token);
        }
        catch(AuthenticationException $ex) {
            $event->setResponse(new Response('autentication failed', 403));
        }
    }
}