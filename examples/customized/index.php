<?php

use Iguan\Common\Data\EncodeDecodeException;
use Iguan\Event\Builder\Builder;
use Iguan\Event\Builder\Config;
use Iguan\Event\Common\CommunicateException;
use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Event;
use Iguan\Event\Subscriber\Subject;
use Iguan\Event\Subscriber\SubjectHttpNotifyWay;
use Iguan\Event\Subscriber\UriPair;
use Iguan\Event\Subscriber\Verificator\InvalidVerificationException;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
include_once __DIR__ . '/MyVerificator.php';
include_once __DIR__ . '/ManCreatedEvent.php';

//load config from file
$config = Config::fromFile(__DIR__ . '/' . 'config.yml');

//set custom value in runtime before passing config to Builder
$config->setValue('subscriber.guard.file.app_version', '1.0.1');
$builder = new Builder($config);

//build emitter with config values
$emitter = $builder->buildEmitter();

try {
    //build subscriber with config values.
    //because subscriber can revoke previous subscriptions, there is may be a server
    //communication issues
    $subscriber = $builder->buildSubscriber();

    //create HTTP-subscription (WebHook) subject for some man creating event
    //assuming we have localhost web root in "examples" folder
    $subject = new Subject(ManCreatedEvent::NAME, new SubjectHttpNotifyWay(new UriPair('http://localhost/extended/', 'index.php')));

    //add some handler for this subject
    $subject->addHandler(function (EventDescriptor $descriptor) {
        //a $manEvent now is source raised ManCreatedEvent
        /** @var ManCreatedEvent $manEvent */
        $manEvent = $descriptor->raisedEvent;
        $man = $manEvent->getMan();

        //just store each new person in separated files with greeting
        file_put_contents('/tmp/event_man_' . $man->getId(), $man->greeting());
    });

    //after adding handlers we must subscribe subject for registering in
    //backend event server and for being ready for receiving incoming events

    //handlers will be notified right here
    $subscriber->subscribe($subject);

    //fire event with custom event wrapper
    $emitter->dispatch(ManCreatedEvent::compose(1199, 'John', 28));
} catch (CommunicateException $e) {
    die('Some server communication error: ' . $e->getMessage());
} catch (EncodeDecodeException $e) {
    die('Some data decoding error: ' . $e->getMessage());
} catch (InvalidVerificationException $e) {
    //each payload data are signed by event server using private key
    die('Payload not trusted: ' . $e->getMessage());
}

