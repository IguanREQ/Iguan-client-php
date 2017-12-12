<?php

namespace Iguan\Event\Subscriber;


use Iguan\Common\Data\DataDecoder;
use Iguan\Common\Data\JsonDataDecoder;
use Iguan\Event\Common\CommonAuth;
use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Event;
use Iguan\Event\EventBundle;

class GlobalEventExtractor
{
    private static $WAYS_INCOMING_DESCRIPTORS_CACHE = [];

    /**
     * @var CommonAuth
     */
    private $auth;
    /**
     * @var DataDecoder
     */
    private $decoder;

    public function __construct(CommonAuth $auth, DataDecoder $decoder)
    {
        $this->auth = $auth;
        $this->decoder = $decoder;
    }

    /**
     * @param SubjectNotifyWay $way
     * @throws \Iguan\Common\Data\JsonException
     * @throws \Iguan\Common\Data\EncodeDecodeException
     */
    public function extract(SubjectNotifyWay $way)
    {
        if (isset(self::$WAYS_INCOMING_DESCRIPTORS_CACHE[get_class($way)])) {
            $eventDescriptors = self::$WAYS_INCOMING_DESCRIPTORS_CACHE[get_class($way)];
        } else {
            $auth = $way->getIncomingAuth();
            if ($this->auth !== null && !$this->auth->equals($auth)) {
                throw new AuthException('Incoming auth does not match with configured value.');
            }

            $serializedData = $way->getIncomingSerializedEvents();
            if (!empty($serializedData)) {
                $jsonDecoder = new JsonDataDecoder();
                $data = $jsonDecoder->decode($serializedData);
                if (!isset($data->events)) {
                    throw new InvalidIncomingData('Incoming events are missed or have incorrect format.');
                }

                $eventDescriptors = $this->parseDescriptors($data->events);
            } else {
                $eventDescriptors = [];
            }
            self::$WAYS_INCOMING_DESCRIPTORS_CACHE[get_class($way)] = $eventDescriptors;
        }

        return $eventDescriptors;
    }

    /**
     * @param array $rawDescriptors
     * @return array
     * @throws \Iguan\Common\Data\EncodeDecodeException
     */
    private function parseDescriptors(array $rawDescriptors)
    {
        $descriptors = [];

        foreach ($rawDescriptors as $rawDescriptor) {
            $descriptors[] = $this->createDescriptor($rawDescriptor);
        }

        return $descriptors;
    }

    /**
     * @param $rawDescriptor
     * @return EventDescriptor
     * @throws \Iguan\Common\Data\EncodeDecodeException
     */
    private function createDescriptor($rawDescriptor)
    {
        $rawDescriptor = $this->decoder->decode($rawDescriptor);

        if (!isset($rawDescriptor->event,
            $rawDescriptor->event->class,
            $rawDescriptor->event->token,
            $rawDescriptor->event->payload,
            $rawDescriptor->sourceTag,
            $rawDescriptor->firedAt,
            $rawDescriptor->delay,
            $rawDescriptor->dispatcher
        )) {
            throw new InvalidIncomingData('Incoming event descriptor are broken or have invalid format.');
        }

        $eventBundle = $this->createEventBundle($rawDescriptor->event);
        $eventClass = $eventBundle->getClass();

        /** @var Event $event */
        $event = new $eventClass();
        $event->unpack($eventBundle);
        $descriptor = new EventDescriptor();
        $descriptor->event = $eventBundle;
        $descriptor->raisedEvent = $event;
        $descriptor->sourceTag = $rawDescriptor->sourceTag;
        $descriptor->delay = $rawDescriptor->delay;
        $descriptor->dispatcher = $rawDescriptor->dispatcher;
        $descriptor->firedAt = $rawDescriptor->firedAt;

        //store cycle dependency
        $event->setPayload($descriptor);
        return $descriptor;
    }

    private function createEventBundle($rawEvent)
    {
        $eventClass = $rawEvent->class;

        if (!class_exists($eventClass)) {
            $eventClass = Event::class;
        }

        $bundle = new EventBundle();
        $bundle->setClass($eventClass);
        $bundle->setPayload($rawEvent->payload);
        $bundle->setToken($rawEvent->token);

        return $bundle;
    }
}