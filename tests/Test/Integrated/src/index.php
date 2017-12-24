<?php
include_once dirname(__DIR__, 4) . '/vendor/autoload.php';

$socketClient = new \Iguan\Common\Remote\SocketClient();
$client = new \Iguan\Event\Common\Remote\RemoteSocketClient($socketClient);
$strategy = new \Iguan\Event\Common\Remote\RemoteCommunicateStrategy($client);
$subscriber = new \Iguan\Event\Subscriber\EventSubscriber('tag', $strategy);

$subject = new \Iguan\Event\Subscriber\Subject('some.event', \Iguan\Event\Subscriber\SubjectNotifyWay::cli(__FILE__));
$subject->addHandler(function (\Iguan\Event\Common\EventDescriptor $descriptor) {
    $encoder = new Iguan\Common\Data\JsonDataEncoder();
    echo $encoder->encode($descriptor->raisedEvent->pack()->asArray());
});
$subscriber->registerOnSubscribe(false);
$subscriber->subscribe($subject);