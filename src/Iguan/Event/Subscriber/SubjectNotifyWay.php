<?php

namespace Iguan\Event\Subscriber;

use Iguan\Event\Common\CommonAuth;

abstract class SubjectNotifyWay
{
    /**
     * @return SubjectNotifyInfo[]
     */
    public function getInfo()
    {
        $info = new SubjectNotifyInfo();
        $info->type = $this->getNotifyWayType();
        $info->extra = $this->getNotifyWayExtra();
        $info->sourceHash = $this->hashCode();
        return [$info];
    }

    public abstract function getIncomingSerializedEvents();

    /**
     * @return CommonAuth
     */
    public abstract function getIncomingAuth();

    public abstract function getNotifyWayType();

    public abstract function getNotifyWayExtra();

    public abstract function hashCode();

    public static function cli($script)
    {
        return new SubjectCliNotifyWay($script);
    }
}