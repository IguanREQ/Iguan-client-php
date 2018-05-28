<?php
/**
 * Created by PhpStorm.
 * User: viirr
 * Date: 12.12.2017
 * Time: 23:17
 */

namespace Test\Subscriber;

use Iguan\Event\Common\CommonAuth;
use Iguan\Event\Subscriber\SubjectCliNotifyWay;
use PHPUnit\Framework\TestCase;

class SubjectNotifyCliWayTest extends TestCase
{
    public function testGettingInfo()
    {
        global $argv;
        $pathToScript = '/path//to/script';
        $argPadding = 2;
        $argv[0] = $pathToScript;
        $argv[$argPadding + 0] = base64_encode('events');
        $argv[$argPadding + 1] = 'token';
        $argv[$argPadding + 2] = 'name';
        $way = new SubjectCliNotifyWay($pathToScript, $argPadding);
        $this->assertEquals(base64_decode($argv[$argPadding + 0]), $way->getIncomingSerializedEvents());

        $wayInfo = $way->getInfo()[0];
        $this->assertEquals(SubjectCliNotifyWay::TYPE, $wayInfo->destType);
        $this->assertEquals($pathToScript, $wayInfo->destPath);
        $this->assertEquals(md5($pathToScript), $wayInfo->sourceHash);
    }
}
