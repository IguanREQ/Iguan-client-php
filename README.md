# Iguan-client-php
_Iguan docs [here](https://iguanreq.gitbook.io/project/)._

High-performance events library for PHP with Web and CLI Hooks with [Iguan-server](https://github.com/IguanREQ/Iguan-server).

### Installing
`composer require iguan-req/iguan-client-php`

### Basic usage

Create Iguan config file (_src/config.yml_):
```yaml
common:
  tag: 'First Event App'
  remote:
    client:
      socket:
        host: <IguanServerIp>
        port: 8081
```

Create event handler/emitter (_src/event.php_)

```php
<?php
​
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
​
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
​
//load config from file
$config = Config::fromFile(__DIR__ . '/' . 'config.yml');
$builder = new Builder($config);
​
//build emitter with config values
$emitter = $builder->buildEmitter();
​
try {
    //build subscriber with config values.
    //because subscriber can revoke previous subscriptions, there is may be a server
    //communication issues
    $subscriber = $builder->buildSubscriber();
​
    //create HTTP-subscription (WebHook) subject for some man creating event
    //assuming we have localhost web root in "src" folder
    $subject = new Subject('man.create', new SubjectHttpNotifyWay(new UriPair('http://localhost/', 'event.php')));
​
    //add some handler for this subject
    $subject->addHandler(function (EventDescriptor $descriptor) {
        //a $man now is source array with event data inside
        $man = $descriptor->raisedEvent->getPayload();
​
        //just store each new person in separated files
        file_put_contents('/tmp/event_man_' . $man['id'], json_encode($man));
    });
​
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
```

Run  local PHP server:
```bash
$ php -S localhost:8000
```

Navigate to http://localhost:8000/src/event.php. Now, you can check tmplocation and look inside generated files!

Note: before usage, make sure that you have ran an [Iguan-server](https://github.com/IguanREQ/Iguan-server).

### More examples

A library was developed for almost zero-configuration, safety and handy running. You can see it in [examples](/examples) by yourself.

A library can be easily extended by using custom realization of almost each class. You can redefine default behavior of everything or
even implement own algorithms, emitters, subscribers very easily.  

### Docs
[Configuration](docs/CONFIG.md)

### Contribution
Feel free to PR!