<?php

namespace Iguan\Event\Common;

/**
 * Class CommonAuth
 * A class data-holder about a way to authenticate
 * remote or local request.
 *
 * @author Vishnevskiy Kirill
 */
class CommonAuth
{
    //if there is no auth
    const AUTH_TYPE_NO_AUTH = 0;

    //auth by token
    const AUTH_TYPE_TOKEN = 1;

    //auth by token name
    const AUTH_TYPE_TOKEN_NAME = 2;

    const MAX_AUTH_TOKEN_LENGTH = 255;
    const MAX_AUTH_TOKEN_NAME_LENGTH = 127;

    private $type = self::AUTH_TYPE_NO_AUTH;
    private $token = '';
    private $tokenName = '';

    /**
     *
     * @param string $token must be not longer MAX_AUTH_TOKEN_LENGTH.
     *               It's like an exactly access token if $tokenName not
     *               presented. Otherwise, it can be a password part.
     * @param string $tokenName a token tag. Can be used as login part.
     */
    public function __construct($token = '', $tokenName = '')
    {
        if (!is_string($token)) {
            throw new \InvalidArgumentException('Auth token must be string. ' . gettype($token) . ' given.');
        }

        if (!is_string($tokenName)) {
            throw new \InvalidArgumentException('Auth token name must be string. ' . gettype($token) . ' given.');
        }

        if (strlen($token) > self::MAX_AUTH_TOKEN_LENGTH) {
            throw new \InvalidArgumentException('Auth token must be <= ' . self::MAX_AUTH_TOKEN_LENGTH . ' bytes. ' . strlen($token) . ' bytes given instead.');
        }

        if (strlen($tokenName) > self::MAX_AUTH_TOKEN_NAME_LENGTH) {
            throw new \InvalidArgumentException('Auth token name must be <= ' . self::MAX_AUTH_TOKEN_NAME_LENGTH . ' bytes. ' . strlen($token) . ' bytes given instead.');
        }

        $isAuthTokenPresent = strlen($token) !== 0;
        if ($isAuthTokenPresent) {
            $this->token = $token;
            $this->type |= self::AUTH_TYPE_TOKEN;
        }

        $isAuthTokenNamePresent = strlen($tokenName) !== 0;
        if ($isAuthTokenNamePresent) {
            $this->tokenName = $tokenName;
            $this->type |= self::AUTH_TYPE_TOKEN_NAME;
        }
    }

    /**
     * @return bool is token present in auth?
     */
    public function isTokenPresent()
    {
        return ($this->type & self::AUTH_TYPE_TOKEN) > 0;
    }

    /**
     * @return bool is token name present in auth?
     */
    public function isTokenNamePresent()
    {
        return ($this->type & self::AUTH_TYPE_TOKEN_NAME) > 0;
    }

    /**
     * A current type of auth.
     * Can be a bitwise sum of class constants if
     * using both token + token name
     *
     * @return int one of class static const
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string current token, if presented. Empty string otherwise.
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string current token name, if presented. Empty string otherwise.
     */
    public function getTokenName()
    {
        return $this->tokenName;
    }

    public function equals(CommonAuth $that)
    {
        if ($that === null) return false;

        return
            $this->type === $that->type &&
            $this->token === $that->token &&
            $this->tokenName === $that->tokenName;
    }
}