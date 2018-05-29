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

//load config from file
$config = Config::fromFile(__DIR__ . '/' . 'config.yml');

//set custom value in runtime before passing config to Builder
//assuming we have event server running on 10.100.0.1:8081
$config->setValue('common.remote.client.socket.host', '10.100.0.1');
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
    $subject = new Subject('man.create', new SubjectHttpNotifyWay(new UriPair('http://localhost/basic/', 'index.php')));

    //add some handler for this subject
    $subject->addHandler(function (EventDescriptor $descriptor) {
        //a $man now is source array with event data inside
        $man = $descriptor->raisedEvent->getPayload();

        //just store each new person in separated files
        file_put_contents('/tmp/event_man_' . $man['id'], json_encode($man));
    });

    //after adding handlers we must subscribe subject for registering in
    //backend event server and for being ready for receiving incoming events

    //handlers will be notified right here
    $subscriber->subscribe($subject);

    //fire event when some person created with person data inside
    $emitter->dispatch(Event::create('man.create', ['name' => 'John', 'age' => 28, 'id' => 1199]));
} catch (CommunicateException $e) {
    die('Some server communication error: ' . $e->getMessage());
} catch (EncodeDecodeException $e) {
    die('Some data decoding error: ' . $e->getMessage());
} catch (InvalidVerificationException $impossible) {
    //verifying disabled
}

