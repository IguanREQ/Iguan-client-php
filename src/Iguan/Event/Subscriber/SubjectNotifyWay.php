<?php

namespace Iguan\Event\Subscriber;

/**
 * Class SubjectNotifyWay.
 * Define a way to be notified.
 * A way info is used for registering on proxy
 * and for fetching data from global state.
 *
 * @author Vishnevskiy Kirill
 */
abstract class SubjectNotifyWay
{
    /**
     * Get info about way for registering on proxy.
     *
     * @return SubjectNotifyInfo[]
     */
    public function getInfo()
    {
        $info = new SubjectNotifyInfo();
        $info->destType = $this->getNotifyWayType();
        $info->destPath = $this->getNotifyWayExtra();
        $info->sourceHash = $this->hashCode();
        return [$info];
    }

    /**
     * Get data piece signed by trusted source
     *
     * @return string
     */
    public abstract function getSignedContextData();

    /**
     * Get trusted source sign
     *
     * @return string
     */
    public abstract function getSign();

    /**
     * Fetch serialized incoming data from globals.
     *
     * @return string
     */
    public abstract function getIncomingSerializedEvents();


    /**
     * Get a way identifier.
     *
     * @return int
     */
    public abstract function getNotifyWayType();

    /**
     * Get a way extra data, i.e. file path or
     * remote script location.
     *
     * @return mixed
     */
    public abstract function getNotifyWayExtra();

    /**
     * Hash of way instance.
     * Different ways must returns different ways.
     *
     * @return string
     */
    public abstract function hashCode();

    /**
     * Factory method for SubjectCliNotifyWay
     *
     * @param $script
     * @return SubjectCliNotifyWay
     */
    public static function cli($script)
    {
        return new SubjectCliNotifyWay($script);
    }
}