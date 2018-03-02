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
        'token' => [
            'types' => ['string']
        ],
        'token_name' => [
            'types' => ['string']
        ],
        'class' => CommonAuth::class
    ];

    /**
     * @return CommonAuth
     */
    public function create()
    {
        $token = $this->getExceptedConfigValue('token', '');
        $token_name = $this->getExceptedConfigValue('token_name', '');
        $class = $this->getExceptedConfigValue('class', CommonAuth::class);

        return new $class($token, $token_name);
    }
}