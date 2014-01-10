<?php

namespace Bazalt\Auth\Webservice;

use Bazalt\Data\Validator;

class JWTWebservice extends \Bazalt\Rest\Resource
{
    protected function getIssParam()
    {
        $type = 'bearer ';
        if (isset($_SERVER['HTTP_AUTHORIZATION']) && strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']), $type) === 0) {
            $token = substr($_SERVER['HTTP_AUTHORIZATION'], strlen($type));

            if ($token && $token != 'null') {
                $config = \Bazalt\Config::container();
                $token = \JWT::decode($token, $config['jwt.key']);

                // validate
                if ($token) {
                    if ($token->aud != \Bazalt\Site::get()->getUrl()) {
                        throw new \UnexpectedValueException('Invalid `aud` parameter');
                    }
                    $time = time();
                    if ($time < $token->nbf || $time > $token->exp) {
                        throw new \UnexpectedValueException('Token expired');
                    }
                    return $token->iss;
                }
            }
        }
        return null;
    }

    protected function getJWTUser()
    {
        $userId = $this->getIssParam();
        $user = ($userId) ? \Bazalt\Auth\Model\User::getById((int)$userId) :
                            \Bazalt\Auth\Model\Guest::create(null);

        return $user;
    }

    /**
     * @param \Bazalt\Auth\Model\User $user
     * @param $expireTime 31 day * 24 hours * 60 min * 60 sec
     * @return mixed
     */
    protected function getJWTToken(\Bazalt\Auth\Model\User $user, $expireTime = 2678400)
    {
        $token = array(
            'iss' => $user->id,
            'aud' => \Bazalt\Site::get()->getUrl(),
            'exp' => time() + $expireTime,
            'nbf' => time()
        );

        $config = \Bazalt\Config::container();
        return \JWT::encode($token, $config['jwt.key']);
    }
}