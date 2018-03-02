<?php
/**
 * Created by PhpStorm.
 * User: viirr
 * Date: 18.11.2017
 * Time: 23:29
 */

namespace Test\Test\Dispatcher;

use Iguan\Common\Remote\SocketClient;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoteSocketClientTest
 * @author Vishnevskiy Kirill
 */
class RemoteSocketClientTest extends TestCase
{
    private $socketServerResource;

    protected function setUp()
    {
        $this->socketServerResource = proc_open('php "' . __DIR__ . DIRECTORY_SEPARATOR . 'echo_socket_server.php"', [], $pipes);
    }

    protected function tearDown()
    {
        proc_close($this->socketServerResource);
    }

    public function testWritingEchoReading()
    {
        $socketClient = new SocketClient('tcp://127.0.0.1:16986');
        $dataLength = 1024 * 1024;
        $data = openssl_random_pseudo_bytes($dataLength);
        $socketClient->write($data);

        $writtenData = $socketClient->read($dataLength);

        $this->assertEquals($data, $writtenData);
    }

}
