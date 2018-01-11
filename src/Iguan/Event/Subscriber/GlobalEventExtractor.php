<?php

namespace Iguan\Event\Subscriber;


use Iguan\Common\Data\DataDecoder;
use Iguan\Common\Data\JsonDataDecoder;
use Iguan\Event\Common\CommonAuth;
use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Event;
use Iguan\Event\EventBundle;

/**
 * Class GlobalEventExtractor.
 * Extract and create events from global inputs.
 *
 * @author Vishnevskiy Kirill
 */
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

    /**
     * GlobalEventExtractor constructor.
     *
     * @param CommonAuth $auth
     * @param DataDecoder $decoder
     */
    public function __construct(CommonAuth $auth, DataDecoder $decoder)
    {
        $this->auth = $auth;
        $this->decoder = $decoder;
    }

    /**
     * Extract events from globals using
     * notify way. A way is know how to extract
     * raw input. Method will decode raw input data
     * and convert it to the instances of EventDescriptors.
     * Incoming raw data also will be checked for
     * matching with current auth data.
     *
     * @param SubjectNotifyWay $way a notify way for which need to extract events
     * @return EventDescriptor[] incoming events for passed way
     *
     * @throws \Iguan\Common\Data\EncodeDecodeException if incoming data is incorrect
     */
    public function extract(SubjectNotifyWay $way)
    {
        $wayClass = get_class($way);

        //the same way has SAME input data, so, we can
        //store it at first time and retrieve data without
        //context in future
        if (isset(self::$WAYS_INCOMING_DESCRIPTORS_CACHE[$wayClass])) {
            $eventDescriptors = self::$WAYS_INCOMING_DESCRIPTORS_CACHE[$wayClass];
        } else {
            $auth = $way->getIncomingAuth();

            if (!$auth->equals($this->auth)) {
                throw new AuthException('Incoming event auth does not match with configured value.');
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
            self::$WAYS_INCOMING_DESCRIPTORS_CACHE[$wayClass] = $eventDescriptors;
        }

        return $eventDescriptors;
    }

    /**
     * Create descriptors from raw serialized input.
     *
     * @param string[] $rawDescriptors
     * @return EventDescriptor[]
     *
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
     * Create descriptor from raw serialized event.
     *
     * @param string $rawDescriptor
     * @return EventDescriptor
     *
     * @throws \Iguan\Common\Data\EncodeDecodeException
     */
    private function createDescriptor($rawDescriptor)
    {
        $rawDescriptor = $this->decoder->decode($rawDescriptor);
        
        if (!isset($rawDescriptor->event,
                $rawDescriptor->event->class,
                $rawDescriptor->event->token,
                $rawDescriptor->event->payloadType,
                $rawDescriptor->sourceTag,
                $rawDescriptor->firedAt,
                $rawDescriptor->delay,
                $rawDescriptor->dispatcher
                //may be 'null'
            ) || !property_exists($rawDescriptor->event, 'payload')) {
            throw new InvalidIncomingData('Incoming event descriptor are broken or have invalid format.');
        }

        $eventBundle = $this->createEventBundle($rawDescriptor->event);
        $eventClass = $eventBundle->getClass();

        /** @var Event $event */
        $event = new $eventClass();
        $event->unpack($eventBundle);
        $descriptor = new EventDescriptor();
        $descriptor->event = $eventBundle->asArray();
        $descriptor->raisedEvent = $event;
        $descriptor->sourceTag = $rawDescriptor->sourceTag;
        $descriptor->delay = $rawDescriptor->delay;
        $descriptor->dispatcher = $rawDescriptor->dispatcher;
        $descriptor->firedAt = $rawDescriptor->firedAt;

        //store cycle dependency
        $event->setDescriptor($descriptor);
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
        $bundle->setPayloadType($rawEvent->payloadType);
        $bundle->setPayload($rawEvent->payload);
        $bundle->setToken($rawEvent->token);

        return $bundle;
    }
}