<?php
/**
 * Created by PhpStorm.
 * User: viirr
 * Date: 18.11.2017
 * Time: 12:05
 */

namespace Test\Dispatcher;

use Iguan\Common\Remote\SocketClient;


/**
 * Class StubSocketClient
 * @author Vishnevskiy Kirill
 */
class StubSocketClient extends SocketClient
{
    private $writtenData;

    public function write($data)
    {
        $this->writtenData = $data;
    }

    public function getWrittenData()
    {
        return $this->writtenData;
    }
}