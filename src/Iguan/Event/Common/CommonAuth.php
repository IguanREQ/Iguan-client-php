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
    const AUTH_TYPE_NO_AUTH = 1;

    //auth by login (secret)
    const AUTH_TYPE_LOGIN = 2;

    //auth by password
    const AUTH_TYPE_PASSWORD = 4;

    const MAX_AUTH_LOGIN_LENGTH = 255;
    const MAX_AUTH_PASSWORD_LENGTH = 127;

    private $type = self::AUTH_TYPE_NO_AUTH;
    private $login = '';
    private $password = '';

    /**
     *
     * @param string $login must be not longer MAX_AUTH_LOGIN_LENGTH.
     * @param string $password a token tag.
     */
    public function __construct($login = '', $password = '')
    {
        if (!is_string($login)) {
            throw new \InvalidArgumentException('Auth login must be string. ' . gettype($login) . ' given.');
        }

        if (!is_string($password)) {
            throw new \InvalidArgumentException('Auth password must be string. ' . gettype($login) . ' given.');
        }

        if (strlen($login) > self::MAX_AUTH_LOGIN_LENGTH) {
            throw new \InvalidArgumentException('Auth login be <= ' . self::MAX_AUTH_LOGIN_LENGTH . ' bytes. ' . strlen($login) . ' bytes given instead.');
        }

        if (strlen($password) > self::MAX_AUTH_PASSWORD_LENGTH) {
            throw new \InvalidArgumentException('Auth password must be <= ' . self::MAX_AUTH_PASSWORD_LENGTH . ' bytes. ' . strlen($login) . ' bytes given instead.');
        }

        $isLoginPresent = strlen($login) !== 0;
        if ($isLoginPresent) {
            $this->login = $login;
            $this->type = self::AUTH_TYPE_LOGIN;
        }

        $isPasswordPresent = strlen($password) !== 0;
        if ($isPasswordPresent) {
            $this->password = $password;
            $this->type |= self::AUTH_TYPE_PASSWORD;
        }
    }

    /**
     * @return bool is token present in auth?
     */
    public function isTokenPresent()
    {
        return ($this->type & self::AUTH_TYPE_LOGIN) > 0;
    }

    /**
     * @return bool is token name present in auth?
     */
    public function isTokenNamePresent()
    {
        return ($this->type & self::AUTH_TYPE_PASSWORD) > 0;
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
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string current token name, if presented. Empty string otherwise.
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function equals(CommonAuth $that)
    {
        if ($that === null) return false;

        return
            $this->type === $that->type &&
            $this->login === $that->login &&
            $this->password === $that->password;
    }
}