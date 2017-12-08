<?php

namespace Iguan\Event\Subscriber;


use Iguan\Event\Common\CommonAuth;

class SubjectCliNotifyWay extends SubjectNotifyWay
{
    const TYPE = 1;

    public function getIncomingSerializedEvents()
    {
        global $argv;

        //todo dynamic define
        return $argv[1];
    }

    public function getNotifyWayType()
    {
        return self::TYPE;
    }

    public function getNotifyWayExtra()
    {
        // TODO: Implement getNotifyWayExtra() method.
    }

    public function getIncomingAuth()
    {
        global $argv;

        return new CommonAuth(isset($argv[2]) ? $argv[2] : null, isset($argv[3]) ? $argv[3] : null);
    }
}