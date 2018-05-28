<?php

use Iguan\Event\Builder\Builder;
use Iguan\Event\Builder\Config;
use Iguan\Event\Subscriber\Subject;
use Iguan\Event\Subscriber\SubjectHttpNotifyWay;
use Iguan\Event\Subscriber\UriPair;

include_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$config = Config::fromFile(__DIR__ . DIRECTORY_SEPARATOR . 'config.yml');
$builder = new Builder($config);
$emitter = $builder->buildEmitter();
$subscriber = $builder->buildSubscriber();

$subject = new Subject('some.*', new SubjectHttpNotifyWay(new UriPair('http://10.100.200.3/', 'event.php')));
$subject->addHandler(function (\Iguan\Event\Common\EventDescriptor $descriptor) {
    $encoder = new Iguan\Common\Data\JsonDataEncoder();
    echo $encoder->encode($descriptor->raisedEvent->pack()->asArray());
});

$subscriber->register($subject);

$emitter->dispatch(\Iguan\Event\Event::create('some.event', ['hello' => 'world']));
$emitter->dispatch(\Iguan\Event\Event::create('some.event2', ['hello' => 'world']));