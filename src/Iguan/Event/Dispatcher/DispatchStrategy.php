<?php

namespace Iguan\Event\Dispatcher;

/**
 * Class DispatchStrategy.
 * A base class for dispatching realizing.
 * If you wanna custom event handling in app,
 * just extends that class, realize DispatchStrategy::emitEvent
 * and set an strategy instance to EventDispatcher.
 *
 * @author Vishnevskiy Kirill
 */
abstract class DispatchStrategy
{
    const MAX_AUTH_TOKEN_LENGTH = 255;
    const MAX_AUTH_TOKEN_NAME_LENGTH = 127;

    private $authToken;
    private $authTokenName;

    /**
     * Emit event according to current strategy.
     *
     * @param EventDescriptor $descriptor event describer structure that must
     *                        be passed to recipient.
     *
     * @throws EventDispatchException in case of any dispatch error
     */
    public abstract function emitEvent(EventDescriptor $descriptor);

    /**
     * Set auth token and token name, that will be passed in fire event
     * request to recipient.
     *
     *
     * @param string $token must be not longer MAX_AUTH_TOKEN_LENGTH.
     *               It's like an exactly access token if $tokenName not
     *               presented. Otherwise, it can be a password part.
     * @param string $tokenName a token tag. Can be used as login part.
     */
    public function setAuthToken($token, $tokenName = '')
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

        $this->authToken = $token;
        $this->authTokenName = $tokenName;
    }

    /**
     * @return string current token, if presented. Empty string otherwise.
     */
    protected function getAuthToken()
    {
        return $this->authToken === null ? '' : $this->authToken;
    }

    /**
     * @return string current token name, if presented. Empty string otherwise.
     */
    protected function getAuthTokenName()
    {
        return $this->authTokenName === null ? '' : $this->authTokenName;
    }
}