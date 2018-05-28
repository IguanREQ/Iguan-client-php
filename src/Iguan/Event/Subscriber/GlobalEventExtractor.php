<?php

namespace Iguan\Event\Subscriber;

use Iguan\Common\Data\DataDecoder;
use Iguan\Common\Data\JsonDataDecoder;
use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Event;
use Iguan\Event\EventBundle;
use Iguan\Event\Subscriber\Verificator\InvalidVerificationException;
use Iguan\Event\Subscriber\Verificator\Verificator;

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
     * @var DataDecoder
     */
    private $decoder;
    /**
     * @var Verificator
     */
    private $verificator;

    /**
     * GlobalEventExtractor constructor.
     *
     * @param DataDecoder $decoder
     * @param Verificator $verificator
     */
    public function __construct(DataDecoder $decoder, Verificator $verificator)
    {
        $this->decoder = $decoder;
        $this->verificator = $verificator;
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
     * @throws InvalidVerificationException
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
            if (!$this->verificator->isVerified($way)) {
                throw new InvalidVerificationException('Incoming event cannot be trusted.');
            }

            $serializedData = $way->getIncomingSerializedEvents();
            if (!empty($serializedData)) {
                $jsonDecoder = new JsonDataDecoder();
                $data = $jsonDecoder->decode($serializedData);
                if (!isset($data->events)) {
                    throw new InvalidIncomingDataException('Incoming events are missed or have incorrect format.');
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
                $rawDescriptor->event->name,
                $rawDescriptor->event->payloadType,
                $rawDescriptor->sourceTag,
                $rawDescriptor->firedAt,
                $rawDescriptor->delay,
                $rawDescriptor->dispatcher
                //may be 'null'
            ) || !property_exists($rawDescriptor->event, 'payload')) {
            throw new InvalidIncomingDataException('Incoming event descriptor are broken or have invalid format.');
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

        //check if we have incoming class and given class are subclass of Event::class
        if (!(class_exists($eventClass) && is_subclass_of($eventClass, Event::class))) {
            $eventClass = Event::class;
        }

        $bundle = new EventBundle();
        $bundle->setClass($eventClass);
        $bundle->setPayloadType($rawEvent->payloadType);
        $bundle->setPayload($rawEvent->payload);
        $bundle->setName($rawEvent->name);

        return $bundle;
    }
}