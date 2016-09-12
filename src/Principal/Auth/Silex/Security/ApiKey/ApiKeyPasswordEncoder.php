<?php
namespace Principal\Auth\Silex\Security\ApiKey;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Class ApiKeyPasswordEncoder
 * @package Principal\Auth\Silex\Security\ApiKey
 */
class ApiKeyPasswordEncoder implements PasswordEncoderInterface {

    /**
     * @param $raw
     * @param $salt
     * @return mixed
     */
    public function encodePassword($raw, $salt) {
        return $raw;
    }

    /**
     * @param $encoded
     * @param $raw
     * @param $salt
     * @return bool
     */
    public function isPasswordValid($encoded, $raw, $salt) {
       return $encoded == $raw;
    }
}