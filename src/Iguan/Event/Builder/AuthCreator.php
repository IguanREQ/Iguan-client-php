<?php

namespace Iguan\Event\Builder;

use Iguan\Event\Common\CommonAuth;

/**
 * Class AuthCreator
 * Creator for 'common.auth' key of config.
 *
 * @author Vishnevskiy Kirill
 */
class AuthCreator extends Creator
{
    const EXCEPTED_CONFIG_VALUES = [
        'login' => [
            'types' => ['string']
        ],
        'password' => [
            'types' => ['string']
        ],
        'class' => CommonAuth::class
    ];

    /**
     * @return CommonAuth
     */
    public function create()
    {
        $token = $this->getExceptedConfigValue('login', '');
        $token_name = $this->getExceptedConfigValue('password', '');
        $class = $this->getExceptedConfigValue('class', CommonAuth::class);

        return new $class($token, $token_name);
    }
}