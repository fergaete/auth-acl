<?php
namespace Principal\Auth\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Principal\Auth\Silex\Security\Http\Firewall\ApiKeyAuthenticationListener;
use Principal\Auth\Silex\Security\ApiKey\ApiKeyAuthenticationProvider;

/**
 * Class ApiKeyServiceProvider
 * @package Principal\Auth\Silex\Provider
 */
class ApiKeyServiceProvider implements ServiceProviderInterface {

    /**
     * @param Application $app
     */
    public function register(Application $app) {
        $app['security.authentication_listener.factory.api_key'] = $app->protect(
            function ($name, $options) use ($app) {
                unset($options); // not in use
                $app['security.authentication_listener.'.$name.'.api_key'] = $app->share(
                    function () use ($app) {
                        return new ApiKeyAuthenticationListener(
                            $app['security'],
                            $app['security.authentication_manager']
                        );
                    }
                );
                $app['security.authentication_provider.'.$name.'.api_key'] = $app->share(
                    function () use ($app) {
                        return new ApiKeyAuthenticationProvider(
                            $app['api_key.user_provider'],
                            $app['api_key.password_encoder']
                        );
                    }
                );
                return array(
                    'security.authentication_provider.' . $name . '.api_key',
                    'security.authentication_listener.' . $name . '.api_key',
                    null,      // the entry point id
                    'pre_auth' // the position of the listener in the stack
                );
            }
        );
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app) {}
}