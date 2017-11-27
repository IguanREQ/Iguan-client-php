<?php

namespace Iguan\Event\Common;

use Iguan\Common\Remote\SocketClient;

/**
 * Class RemoteSocketClient
 * A way to communicate with server over socket
 * using instance of SocketClient.
 *
 * @author Vishnevskiy Kirill
 */
class RemoteSocketClient extends RemoteClient
{
    /**
     * @var RemoteSocketClient
     */
    private $client;

    public function __construct(SocketClient $client)
    {
        $this->client = $client;
    }

    /**
     * First byte - auth type byte (bit mask of self::AUTH_TYPE_* const)
     * Next, if has an AUTH_TYPE_TOKEN bit - first byte it's a token size in bytes, next - N bytes of token.
     * Next, if has an AUTH_TYPE_TOKEN_NAME bit - first byte it's a token name size in bytes, next - N bytes of token name.
     * Next - payload data.
     * LF byte at the end required!
     *
     * @param string $payload a data to be written as body
     * @param CommonAuth $commonAuth an extra data for authorization on remote
     *                   if null - no auth
     */
    public function write($payload, CommonAuth $commonAuth = null)
    {
        if ($commonAuth !== null) {
            $authType = pack('C', $commonAuth->getType());
        } else {
            $authType = pack('C', CommonAuth::AUTH_TYPE_NO_AUTH);
        }

        $message = $authType;

        if ($commonAuth !== null) {
            if ($commonAuth->isTokenPresent()) {
                $authToken = $commonAuth->getToken();
                $message .= pack('C', strlen($authToken)) . $authToken;
            }

            if ($commonAuth->isTokenNamePresent()) {
                $authTokenName = $commonAuth->getTokenName();
                $message .= pack('C', strlen($authTokenName)) . $authTokenName;
            }
        }

        $message .= $payload . "\n";

        $this->client->write($message);
    }

    /**
     * Read data from remote using current communication type.
     * Socket must be initialized!
     * A server response must should fit in 8196 bytes.
     *
     * @return string server response
     */
    public function read()
    {
        return $this->client->readChunk(8196);
    }
}